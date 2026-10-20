<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\RUCSSQueueRunner;

use ActionScheduler_ActionClaim;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_Store;
use Brain\Monkey\Functions;
use DateTime;
use Mockery;
use ReflectionClass;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner::do_batch
 *
 * @group RUCSSQueueRunner
 */
class Test_DoBatch extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$now     = new DateTime();
		$store   = Mockery::mock( ActionScheduler_Store::class );
		$monitor = Mockery::mock( ActionScheduler_FatalErrorMonitor::class );
		$claim   = Mockery::mock( ActionScheduler_ActionClaim::class );

		Functions\expect( 'as_get_datetime_object' )->once()->andReturn( $now );
		Functions\when( 'wp_using_ext_object_cache' )->justReturn( false );
		Functions\when( 'wp_cache_flush' )->justReturn( true );

		$store->expects( 'query_actions' )
			->once()
			->with(
				[
					'group'        => 'rocket-rucss',
					'status'       => ActionScheduler_Store::STATUS_PENDING,
					'claimed'      => false,
					'date'         => $now,
					'date_compare' => '<=',
					'per_page'     => 1,
					'orderby'      => 'none',
				]
			)
			->andReturn( $config['claimable'] );

		if ( $expected['claims'] ) {
			$store->expects( 'stake_claim' )
				->once()
				->with( $config['size'], null, [], 'rocket-rucss' )
				->andReturn( $claim );
			$claim->expects( 'get_actions' )->andReturn( $config['claim_actions'] );
			$monitor->expects( 'attach' )->once()->with( $claim );
			$monitor->expects( 'detach' )->once();
			$store->expects( 'release_claim' )->once()->with( $claim );
		} else {
			$store->shouldNotReceive( 'stake_claim' );
			$store->shouldNotReceive( 'release_claim' );
			$monitor->shouldNotReceive( 'attach' );
		}

		$reflection = new ReflectionClass( RUCSSQueueRunner::class );
		$runner     = $reflection->newInstanceWithoutConstructor();

		foreach ( [ 'store' => $store, 'monitor' => $monitor ] as $name => $value ) {
			$property = new \ReflectionProperty( \ActionScheduler_Abstract_QueueRunner::class, $name );
			$property->setAccessible( true );
			$property->setValue( $runner, $value );
		}

		$method = $reflection->getMethod( 'do_batch' );
		$method->setAccessible( true );

		$this->assertSame( $expected['processed'], $method->invoke( $runner, $config['size'], 'WP Cron' ) );
	}
}
