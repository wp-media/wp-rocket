<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\RUCSSQueueRunner;

use ActionScheduler_ActionClaim;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_Store;
use Brain\Monkey\Functions;
use DateTime;
use Mockery;
use ReflectionMethod;
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
		$runner  = Mockery::mock( RUCSSQueueRunner::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$actions = $config['scheduled'] ? [ 5 ] : [];

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
			->andReturn( $actions );

		if ( $config['scheduled'] ) {
			$store->expects( 'stake_claim' )
				->once()
				->with( 25, null, [], 'rocket-rucss' )
				->andReturn( $claim );
			$claim->expects( 'get_actions' )->andReturn( $actions );
			$claim->expects( 'get_id' )->andReturn( 1 );
			$store->expects( 'find_actions_by_claim_id' )->with( 1 )->andReturn( $actions );
			$runner->expects( 'process_action' )->once()->with( 5, 'WP Cron' );
			$runner->expects( 'batch_limits_exceeded' )->once()->with( 1 )->andReturn( false );
			$monitor->expects( 'attach' )->once()->with( $claim );
			$monitor->expects( 'detach' )->once();
			$store->expects( 'release_claim' )->once()->with( $claim );
		} else {
			$store->shouldNotReceive( 'stake_claim' );
			$store->shouldNotReceive( 'release_claim' );
			$monitor->shouldNotReceive( 'attach' );
			$runner->shouldNotReceive( 'process_action' );
		}

		foreach ( [ 'store' => $store, 'monitor' => $monitor ] as $name => $value ) {
			$property = new \ReflectionProperty( \ActionScheduler_Abstract_QueueRunner::class, $name );
			$property->setAccessible( true );
			$property->setValue( $runner, $value );
		}

		$method = new ReflectionMethod( RUCSSQueueRunner::class, 'do_batch' );
		$method->setAccessible( true );

		$this->assertSame( $expected, $method->invoke( $runner, 25, 'WP Cron' ) );
	}
}
