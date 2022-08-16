<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket_Mobile_Detect;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Controller to load initial tasks.
	 *
	 * @var LoadInitialSitemap
	 */
	protected $controller;

	/**
	 * Clear cache controller.
	 *
	 * @var ClearCache
	 */
	protected $clear_cache;

	/**
	 * Cache query instance.
	 *
	 * @var Cache
	 */
	private $query;

	/**
	 * Activation manager.
	 *
	 * @var Activation
	 */
	protected $activation;

	/**
	 * Mobile detector instance.
	 *
	 * @var WP_Rocket_Mobile_Detect
	 */
	protected $mobile_detect;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data            $options Options instance.
	 * @param LoadInitialSitemap      $controller controller creating the initial task.
	 * @param Cache                   $query Cache query instance.
	 * @param Activation              $activation Activation manager.
	 * @param WP_Rocket_Mobile_Detect $mobile_detect Mobile detector instance.
	 * @param ClearCache              $clear_cache Clear cache controller.
	 */
	public function __construct( Options_Data $options, LoadInitialSitemap $controller, $query, Activation $activation, WP_Rocket_Mobile_Detect $mobile_detect, ClearCache $clear_cache ) {
		$this->options       = $options;
		$this->controller    = $controller;
		$this->query         = $query;
		$this->activation    = $activation;
		$this->mobile_detect = $mobile_detect;
		$this->clear_cache   = $clear_cache;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'update_option_' . WP_ROCKET_SLUG     => [
				[ 'maybe_load_initial_sitemap', 10, 2 ],
				[ 'maybe_cancel_preload', 10, 2 ],
			],
			'rocket_after_process_buffer'         => 'update_cache_row',
			'rocket_deactivation'                 => 'on_deactivation',
			'permalink_structure_changed'         => 'on_permalink_changed',
			'wp_rocket_upgrade'                   => [ 'on_update', 16, 2 ],
			'rocket_rucss_complete_job_status'    => 'clean_url',
			'rocket_rucss_after_clearing_usedcss' => [ 'clean_url', 20 ],
		];
	}

	/**
	 * Load first tasks from preload when preload option is enabled.
	 *
	 * @param array $old_value old configuration values.
	 * @param array $value new configuration values.
	 * @return void
	 */
	public function maybe_load_initial_sitemap( $old_value, $value ) {
		if ( ! isset( $value['manual_preload'], $old_value['manual_preload'] ) ) {
			return;
		}

		if ( $value['manual_preload'] === $old_value['manual_preload'] ) {
			return;
		}

		if ( ! $value['manual_preload'] ) {
			return;
		}

		$this->controller->load_initial_sitemap();
	}

	/**
	 * Cancel preload when configuration from sitemap changed.
	 *
	 * @param array $old_value old configuration values.
	 * @param array $value new configuration values.
	 * @return void
	 */
	public function maybe_cancel_preload( $old_value, $value ) {
		if ( ! isset( $value['manual_preload'], $old_value['manual_preload'] ) ) {
			return;
		}

		if ( $value['manual_preload'] === $old_value['manual_preload'] ) {
			return;
		}

		if ( $value['manual_preload'] ) {
			return;
		}

		$this->controller->cancel_preload();
	}

	/**
	 * Create or update the cache row after processing the buffer
	 *
	 * @return void
	 */
	public function update_cache_row() {
		global $wp;
		$url = home_url( add_query_arg( [], $wp->request ) );

		if ( $this->query->is_preloaded( $url ) ) {
			$detected = $this->mobile_detect->isMobile() && ! $this->mobile_detect->isTablet() ? 'mobile' : 'desktop';

			/**
			 * Fires when the preload from an URL is completed.
			 *
			 * @param string $url URL preladed.
			 * @param string $device Device from the cache.
			 */
			do_action( 'rocket_preload_completed', $url, $detected );
		}

		if ( str_contains( '?', $url ) || ( $this->query->is_pending( $url ) && $this->options->get( 'do_caching_mobile_files', false ) ) ) {
			return;
		}

		$this->query->create_or_update(
			[
				'url'           => $url,
				'status'        => 'completed',
				'last_accessed' => true,
			]
		);
	}

	/**
	 * Delete url from the Preload when a 404 is risen.
	 *
	 * @return void
	 */
	public function delete_url_on_not_found() {
		global $wp;
		$url = home_url( $wp->request );
		$this->query->delete_by_url( $url );
	}

	/**
	 * Preload on permalink changed.
	 *
	 * @return void
	 */
	public function on_permalink_changed() {
		$this->query->remove_all();
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
		$this->activation->on_update( $new_version, $old_version );
	}

	/**
	 * Clear preload on deactivation.
	 *
	 * @return void
	 */
	public function on_deactivation() {
		$this->activation->deactivation();
	}

	/**
	 * Clean the url.
	 *
	 * @param string $url url.
	 * @return void
	 */
	public function clean_url( string $url ) {

		$this->clear_cache->partial_clean( [ $url ] );
	}
}
