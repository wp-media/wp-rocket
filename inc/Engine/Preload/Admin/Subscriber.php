<?php

namespace WP_Rocket\Engine\Preload\Admin;

use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\ClearCache;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Clear cache controller.
	 *
	 * @var ClearCache
	 */
	protected $controller;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Preload queue runner.
	 *
	 * @var PreloadQueueRunner
	 */
	protected $queue_runner;

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data       $options Options instance.
	 * @param Settings           $settings Settings instance.
	 * @param ClearCache         $clear_cache Clear cache controller.
	 * @param Queue              $queue preload queue.
	 * @param PreloadQueueRunner $preload_queue_runner preload queue runner.
	 * @param Logger             $logger logger instance.
	 */
	public function __construct( Options_Data $options, Settings $settings, ClearCache $clear_cache, Queue $queue, PreloadQueueRunner $preload_queue_runner, Logger $logger ) {
		$this->options      = $options;
		$this->settings     = $settings;
		$this->controller   = $clear_cache;
		$this->queue        = $queue;
		$this->queue_runner = $preload_queue_runner;
		$this->logger       = $logger;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'             => [ 'maybe_display_preload_notice' ],
			'after_rocket_clean_post'   => [ 'clean_partial_cache', 10, 3 ],
			'after_rocket_clean_term'   => [ 'clean_partial_cache', 10, 3 ],
			'rocket_after_clean_terms'  => 'clean_urls',
			'after_rocket_clean_domain' => 'clean_full_cache',
			'init'                      => [ 'maybe_init_preload_queue' ],
		];
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}

	/**
	 * Preload after clearing full cache.
	 *
	 * @return void
	 */
	public function clean_full_cache() {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		$this->controller->full_clean();
	}

	/**
	 * Preload after clearing some cache.
	 *
	 * @param stdClass $object object modified.
	 * @param array    $urls urls cleaned.
	 * @param string   $lang lang from the website.
	 * @return void
	 */
	public function clean_partial_cache( $object, array $urls, $lang ) {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		// Add Homepage URL to $purge_urls for preload.
		$urls[] = get_rocket_i18n_home_url( $lang );

		$urls = array_filter( $urls );
		$this->controller->partial_clean( $urls );
	}

	/**
	 * Clean the list of urls.
	 *
	 * @param array $urls urls.
	 * @return void
	 */
	public function clean_urls( array $urls ) {

		$this->controller->partial_clean( $urls );
	}

	/**
	 * Set the preload queue runner.
	 *
	 * @return void
	 */
	public function maybe_init_preload_queue() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->queue_runner->init();

	}
}
