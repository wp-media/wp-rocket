<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;

class Activation implements ActivationInterface {


	/**
	 * Controller to load initial tasks.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $controller;

	/**
	 * Options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Instantiate class.
	 *
	 * @param LoadInitialSitemap $controller Controller to load initial tasks.
	 * @param Options_Data $options Options.
	 * @param Queue $queue Preload queue.
	 */
	public function __construct( LoadInitialSitemap $controller, Options_Data $options, Queue $queue ) {
		$this->controller = $controller;
		$this->options    = $options;
		$this->queue      = $queue;
	}

	/**
	 * Launch preload on activation.
	 */
	public function activate() {
		if ( ! $this->options->get( 'manual_preload', false ) ) {
			return;
		}
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

		$this->queue->cancel_pending_jobs();

		if ( ! wp_next_scheduled( 'rocket_preload_process_pending' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'rocket_preload_process_pending' );
	}
}
