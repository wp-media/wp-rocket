<?php

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;

class Activation implements ActivationInterface {


	/**
	 * Controller to load initial tasks.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $controller;



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
	 * @param LoadInitialSitemap $controller Controller to load initial tasks.
	 * @param Queue              $queue Preload queue.
	 * @param Cache              $query DB query.
	 */
	public function __construct( LoadInitialSitemap $controller, Queue $queue, Cache $query ) {
		$this->controller = $controller;
		$this->queue      = $queue;
		$this->query      = $query;
	}

	/**
	 * Launch preload on activation.
	 */
	public function activate() {
		do_action( 'admin_init' );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$this->controller->load_initial_sitemap();
	}

	/**
	 * Disable cron and jobs on update.
	 *
	 * @param string $new_version new version from the plugin.
	 * @param string $old_version old version from the plugin.
	 * @return void
	 */
	public function on_update( $new_version, $old_version ) {
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
	 * Launch preload on deactivation.
	 */
	public function deactivation() {
		wp_clear_scheduled_hook( 'rocket_preload_clean_rows_time_event' );
		wp_clear_scheduled_hook( 'rocket_preload_process_pending' );
		wp_clear_scheduled_hook( 'rocket_preload_revert_old_in_progress_rows' );
	}
}
