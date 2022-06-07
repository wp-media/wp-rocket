<?php

namespace WP_Rocket\Engine\Preload\Admin;

use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Preload settings.
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
	 * Instantiate Subscriber.
	 *
	 * @param Options_Data $options Options instance.
	 * @param Settings     $settings Preload settings.
	 * @param ClearCache   $clear_cache Clear cache controller.
	 */
	public function __construct( Options_Data $options, Settings $settings, ClearCache $clear_cache ) {
		$this->options    = $options;
		$this->settings   = $settings;
		$this->controller = $clear_cache;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                          => [ 'maybe_display_preload_notice' ],
			'after_rocket_clean_post'                => [ 'clean_partial_cache', 10, 3 ],
			'after_rocket_clean_term'                => [ 'clean_partial_cache', 10, 3 ],
			'after_rocket_clean_terms'               => [ 'clean_urls', 10, 3 ],
			'rocket_after_preload_after_purge_cache' => [ 'clean_full_cache', 10, 3 ],
		];
	}

	/**
	 * Display a notice while preload is active.
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
}
