<?php
/**
 * Contains tests for the Migration Controller.
 *
 * @package test_cases\migration
 */

use ActionScheduler_StoreSchema as Schema;
use Action_Scheduler\Migration\Controller;
use Action_Scheduler\Migration\Scheduler;

/**
 * Test the migration controller.
 *
 * @group migration
 */
class Controller_Test extends ActionScheduler_UnitTestCase {
	/**
	 * Test to ensure the Migration Controller will schedule the migration.
	 */
	public function test_schedules_migration() {
		as_unschedule_action( Scheduler::HOOK );
		Controller::instance()->schedule_migration();

		$this->assertTrue(
			as_next_scheduled_action( Scheduler::HOOK ) > 0,
			'Confirm that the Migration Controller scheduled the migration.'
		);

		as_unschedule_action( Scheduler::HOOK );
	}

	/**
	 * Test to ensure that if an essential table is missing, the Migration
	 * Controller will not schedule a migration.
	 *
	 * @see https://github.com/woocommerce/action-scheduler/issues/653
	 */
	public function test_migration_not_scheduled_if_tables_are_missing() {
		as_unschedule_action( Scheduler::HOOK );
		$this->rename_claims_table();
		Controller::instance()->schedule_migration();

		$this->assertFalse(
			as_next_scheduled_action( Scheduler::HOOK ),
			'When required tables are missing, the migration will not be scheduled.'
		);

		$this->restore_claims_table_name();
	}

	/**
	 * Rename the claims table, so that it cannot be used by the library.
	 */
	private function rename_claims_table() {
		global $wpdb;
		$normal_table_name   = $wpdb->prefix . Schema::CLAIMS_TABLE;
		$modified_table_name = $normal_table_name . 'x';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "RENAME TABLE {$normal_table_name} TO {$modified_table_name}" );
	}

	/**
	 * Restore the expected name of the claims table, so that it can be used by the library
	 * and any further tests.
	 */
	private function restore_claims_table_name() {
		global $wpdb;
		$normal_table_name   = $wpdb->prefix . Schema::CLAIMS_TABLE;
		$modified_table_name = $normal_table_name . 'x';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "RENAME TABLE {$modified_table_name} TO {$normal_table_name}" );
	}
}
