<?php

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use ActionScheduler_StoreSchema;
use ActionScheduler_LoggerSchema;

class Activation implements ActivationInterface {

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * DB query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Instantiate class.
	 *
	 * @param Queue $queue Preload queue.
	 * @param Cache $query DB query.
	 */
	public function __construct( Queue $queue, Cache $query ) {
		$this->queue = $queue;
		$this->query = $query;
	}

	/**
	 * Launch preload on activation.
	 */
	public function activate() {

		// Recreate AS tables if missing.
		if ( ! $this->is_valid_as_tables() ) {
			$store_schema  = new ActionScheduler_StoreSchema();
			$logger_schema = new ActionScheduler_LoggerSchema();
			$store_schema->register_tables( true );
			$logger_schema->register_tables( true );
		}

		/**
		 * Action that fires before the preload does.
		 */
		do_action( 'rocket_preload_activation' );
		$this->queue->add_job_preload_job_load_initial_sitemap_async();
	}

	/**
	 * Disable cron and jobs on update.
	 *
	 * @param string $new_version new version from the plugin.
	 * @param string $old_version old version from the plugin.
	 * @return void
	 */
	public function clean_on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.12.0', '>=' ) ) {
			return;
		}

		$this->query->remove_all();

		$this->queue->cancel_pending_jobs();

		if ( ! wp_next_scheduled( 'rocket_preload_process_pending' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'rocket_preload_process_pending' );
	}

	/**
	 * Reload sitemap on update.
	 *
	 * @param string $new_version new version from the plugin.
	 * @param string $old_version old version from the plugin.
	 * @return void
	 */
	public function refresh_on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.12.0.2', '>' ) ) {
			return;
		}
		$this->queue->add_job_preload_job_load_initial_sitemap_async();
	}

	/**
	 * Clear preload on deactivation.
	 */
	public function deactivation() {
		wp_clear_scheduled_hook( 'rocket_preload_clean_rows_time_event' );
		wp_clear_scheduled_hook( 'rocket_preload_process_pending' );
		wp_clear_scheduled_hook( 'rocket_preload_revert_old_in_progress_rows' );
	}

	/**
	 * Return true if Action Scheduler tables are correct otherwise false.
	 *
	 * @return bool
	 */
	public function is_valid_as_tables() {
		global $wpdb;

		$exp = "'^" . $wpdb->prefix . "actionscheduler_(logs|actions|groups|claims)$'";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_as_tables = $wpdb->get_col(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare( 'SHOW TABLES FROM ' . DB_NAME . ' WHERE Tables_in_' . DB_NAME . ' LIKE %s AND Tables_in_' . DB_NAME . ' REGEXP ' . $exp, '%actionscheduler%' )
		);

		return 4 === count( $found_as_tables );
	}
}
