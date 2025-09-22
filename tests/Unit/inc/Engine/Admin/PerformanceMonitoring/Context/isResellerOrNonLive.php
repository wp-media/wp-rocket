<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Context;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext::is_reseller_or_non_live
 *
 * @group PerformanceMonitoring
 */
class IsResellerOrNonLive extends TestCase {
	private $options;
	private $user;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->user    = Mockery::mock( User::class );
		$this->context = new PerformanceMonitoringContext( $this->options, $this->user );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $is_reseller, $is_live_site, $expected ) {
		$this->user->shouldReceive( 'is_reseller_account' )
			->once()
			->andReturn( $is_reseller );

		if ( ! $is_reseller ) {
			Functions\expect( 'rocket_is_live_site' )
				->once()
				->andReturn( $is_live_site );
		}

		$this->assertEquals(
			$expected,
			$this->context->is_reseller_or_non_live()
		);
	}
}