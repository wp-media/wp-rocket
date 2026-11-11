<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Abilities\CheckCacheHealth;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Abilities\CheckCacheHealth;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Preload\Abilities\CheckCacheHealth::execute()
 *
 * @group Preload
 * @group Abilities
 */
class ExecuteTest extends TestCase {
	/**
	 * Options_Data mock.
	 *
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options;

	/**
	 * CacheQuery mock.
	 *
	 * @var CacheQuery|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $query;

	/**
	 * Ability instance under test.
	 *
	 * @var CheckCacheHealth
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->query   = $this->createMock( CacheQuery::class );
		$this->ability = new CheckCacheHealth( $this->options, $this->query );

		$this->stubTranslationFunctions();

		if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
			define( 'MINUTE_IN_SECONDS', 60 );
		}

		Functions\when( 'human_time_diff' )->alias(
			function ( $from, $to ) {
				$diff = abs( $to - $from );
				return round( $diff / MINUTE_IN_SECONDS ) . ' minutes';
			}
		);

		// wpm_apply_filters_typed() is left un-stubbed: it is a real function that calls
		// apply_filters(), which Brain Monkey fakes to simply return the given default
		// when no filter is registered, so no explicit stub is needed here.
	}

	private function stubCounts( array $counts ): void {
		$this->query->expects( $this->once() )
			->method( 'get_status_counts' )
			->willReturn( $counts );
	}

	public function testShouldReturnNullEstimateWhenTrackingDisabled(): void {
		$this->stubCounts(
			[
				'pending'     => 10,
				'in-progress' => 0,
				'completed'   => 5,
				'failed'      => 0,
			]
		);

		$this->options->shouldReceive( 'get' )
			->with( 'manual_preload', 0 )
			->andReturn( 0 );

		$this->options->shouldReceive( 'get' )
			->with( 'do_caching_mobile_files', 0 )
			->andReturn( 0 );

		$result = $this->ability->execute();

		$this->assertFalse( $result['tracking_enabled'] );
		$this->assertNull( $result['estimate']['estimated_seconds_remaining'] );
		$this->assertNull( $result['estimate']['estimated_completion_human'] );
		$this->assertTrue( $result['estimate']['is_estimate'] );
		$this->assertNotEmpty( $result['estimate']['method'] );
	}

	public function testShouldReturnNullEstimateWhenNoPendingUrls(): void {
		$this->stubCounts(
			[
				'pending'     => 0,
				'in-progress' => 0,
				'completed'   => 20,
				'failed'      => 0,
			]
		);

		$this->options->shouldReceive( 'get' )->with( 'manual_preload', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'do_caching_mobile_files', 0 )->andReturn( 0 );

		$result = $this->ability->execute();

		$this->assertTrue( $result['tracking_enabled'] );
		$this->assertNull( $result['estimate']['estimated_seconds_remaining'] );
	}

	public function testShouldComputeEstimateWithoutMobileCache(): void {
		$this->stubCounts(
			[
				'pending'     => 90,
				'in-progress' => 0,
				'completed'   => 10,
				'failed'      => 0,
			]
		);

		$this->options->shouldReceive( 'get' )->with( 'manual_preload', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'do_caching_mobile_files', 0 )->andReturn( 0 );

		$result = $this->ability->execute();

		// batch_size defaults to 45, cron_interval defaults to MINUTE_IN_SECONDS (60).
		// ticks = ceil(90/45) = 2; estimated_seconds = 2 * 60 = 120.
		$this->assertSame( 45, $result['estimate']['batch_size'] );
		$this->assertSame( 60, $result['estimate']['cron_interval_seconds'] );
		$this->assertSame( 120, $result['estimate']['estimated_seconds_remaining'] );
		$this->assertFalse( $result['estimate']['mobile_cache_active'] );
	}

	public function testShouldDoubleEstimateWhenMobileCacheActive(): void {
		$this->stubCounts(
			[
				'pending'     => 90,
				'in-progress' => 0,
				'completed'   => 10,
				'failed'      => 0,
			]
		);

		$this->options->shouldReceive( 'get' )->with( 'manual_preload', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'do_caching_mobile_files', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'cache_mobile', 0 )->andReturn( 1 );

		$result = $this->ability->execute();

		// ticks = ceil(90/45) = 2; estimated_seconds = 2 * 60 = 120, doubled to 240 for mobile.
		$this->assertSame( 240, $result['estimate']['estimated_seconds_remaining'] );
		$this->assertTrue( $result['estimate']['mobile_cache_active'] );
		$this->assertStringContainsString( 'mobile', $result['estimate']['method'] );
	}

	public function testShouldMapCountsFromQuery(): void {
		$counts = [
			'pending'     => 3,
			'in-progress' => 1,
			'completed'   => 4,
			'failed'      => 2,
		];

		$this->stubCounts( $counts );

		$this->options->shouldReceive( 'get' )->with( 'manual_preload', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'do_caching_mobile_files', 0 )->andReturn( 0 );

		$result = $this->ability->execute();

		$this->assertSame( $counts, $result['counts'] );
	}
}
