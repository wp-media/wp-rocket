<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Queue\RUCSSQueueRunner;

use ActionScheduler_Action;
use ActionScheduler_DBStore;
use ActionScheduler_NullSchedule;
use ReflectionMethod;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner::do_batch
 *
 * @group RUCSSQueueRunner
 */
class Test_DoBatch extends TestCase {
	private const HOOK = 'rocket_test_rucss_queue_runner_hook';

	private $executed = 0;

	public function set_up() {
		parent::set_up();

		global $wpdb;

		$wpdb->delete( "{$wpdb->prefix}actionscheduler_groups", [ 'slug' => 'rocket-rucss' ] );
		$this->executed = 0;

		add_action( self::HOOK, [ $this, 'count_execution' ] );
	}

	public function tear_down() {
		remove_action( self::HOOK, [ $this, 'count_execution' ] );

		parent::tear_down();
	}

	public function count_execution() {
		++$this->executed;
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$store = new ActionScheduler_DBStore();

		if ( $config['scheduled'] ) {
			$store->save_action( new ActionScheduler_Action( self::HOOK, [], new ActionScheduler_NullSchedule(), 'rocket-rucss' ) );
		}

		$claims_before = $this->countClaims();
		$runner        = new RUCSSQueueRunner( $store );
		$do_batch      = new ReflectionMethod( $runner, 'do_batch' );
		$do_batch->setAccessible( true );

		$this->assertSame( $expected['processed'], $do_batch->invoke( $runner, 25, 'WP Cron' ) );
		$this->assertSame( $expected['executed'], $this->executed );
		$this->assertSame( $claims_before, $this->countClaims() );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__ . '/integration', 'doBatch' )['test_data'];
	}

	private function countClaims(): int {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_claims" );
	}
}
