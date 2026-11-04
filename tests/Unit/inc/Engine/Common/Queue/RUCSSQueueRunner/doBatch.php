<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\RUCSSQueueRunner;

use ActionScheduler_ActionClaim;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_Store;
use Brain\Monkey\Functions;
use DateTime;
use Mockery;
use WP_Rocket\Dependencies\Monolog\Logger as Monologger;
use WP_Rocket\Dependencies\Monolog\Registry;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner::do_batch
 *
 * @group RUCSSQueueRunner
 */
class Test_DoBatch extends TestCase {
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		$this->wpdb = $GLOBALS['wpdb'] ?? null;
	}

	protected function tearDown(): void {
		$GLOBALS['wpdb'] = $this->wpdb;

		Registry::removeLogger( Logger::LOGGER_NAME );
		$this->set_reflective_property( null, 'debug_enabled_cache', Logger::class );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$now     = new DateTime();
		$store   = Mockery::mock( ActionScheduler_Store::class );
		$monitor = Mockery::mock( ActionScheduler_FatalErrorMonitor::class );
		$claim   = Mockery::mock( ActionScheduler_ActionClaim::class );
		$logger  = Mockery::mock( Monologger::class );
		$runner  = Mockery::mock( RUCSSQueueRunner::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$claims  = $config['scheduled'] && ! $config['query_error'];
		$actions = $claims ? [ 5 ] : [];

		$GLOBALS['wpdb'] = (object) [ 'last_error' => $config['query_error'] ? 'Table is marked as crashed' : '' ];

		Registry::addLogger( $logger, Logger::LOGGER_NAME, true );
		$this->set_reflective_property( true, 'debug_enabled_cache', Logger::class );

		Functions\expect( 'as_get_datetime_object' )->once()->andReturn( $now );
		Functions\when( 'wp_using_ext_object_cache' )->justReturn( false );
		Functions\when( 'wp_cache_flush' )->justReturn( true );
		Functions\when( 'esc_html' )->returnArg();

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

		if ( $config['query_error'] ) {
			$logger->expects( 'error' )
				->once()
				->with( 'Unable to query claimable actions. Database error: Table is marked as crashed', [] );
		} else {
			$logger->shouldNotReceive( 'error' );
		}

		if ( $claims ) {
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

		$this->set_reflective_property( $store, 'store', $runner );
		$this->set_reflective_property( $monitor, 'monitor', $runner );

		$this->assertSame( $expected, $this->get_reflective_method( 'do_batch', RUCSSQueueRunner::class )->invoke( $runner, 25, 'WP Cron' ) );
	}
}
