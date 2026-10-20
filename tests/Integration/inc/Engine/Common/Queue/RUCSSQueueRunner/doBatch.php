<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Queue\RUCSSQueueRunner;

use ActionScheduler_Action;
use ActionScheduler_DBStore;
use ActionScheduler_HybridStore;
use ActionScheduler_SimpleSchedule;
use ActionScheduler_Store;
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
		$this->removePostStoreGroup();
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
		$store = $this->getStore( $config['store'] );

		if ( $config['scheduled'] ) {
			$schedule = new ActionScheduler_SimpleSchedule( as_get_datetime_object( '-1 minute' ) );
			$store->save_action( new ActionScheduler_Action( self::HOOK, [], $schedule, 'rocket-rucss' ) );
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

	/**
	 * Builds the store under test.
	 *
	 * The hybrid store is the one Action Scheduler hands out while its migration is incomplete.
	 *
	 * @param string $type Store type, either 'db' or 'hybrid'.
	 *
	 * @return ActionScheduler_Store
	 */
	private function getStore( string $type ): ActionScheduler_Store {
		if ( 'db' === $type ) {
			return new ActionScheduler_DBStore();
		}

		$store = ActionScheduler_Store::instance();

		return $store instanceof ActionScheduler_HybridStore ? $store : new ActionScheduler_HybridStore();
	}

	/**
	 * Removes the rocket-rucss term from the posts store group taxonomy.
	 *
	 * @return void
	 */
	private function removePostStoreGroup(): void {
		global $wpdb;

		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT t.term_id FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE t.slug = %s AND tt.taxonomy = %s",
				'rocket-rucss',
				'action-group'
			)
		);

		foreach ( $term_ids as $term_id ) {
			wp_delete_term( (int) $term_id, 'action-group' );
		}
	}

	private function countClaims(): int {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_claims" );
	}
}
