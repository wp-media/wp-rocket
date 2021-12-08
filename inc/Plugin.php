<?php

namespace WP_Rocket;

use Imagify_Partner;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\ThirdParty\Hostings\HostResolver;

/**
 * Plugin Manager.
 */
class Plugin {

	/**
	 * Instance of Container class.
	 *
	 * @since 3.3
	 *
	 * @var Container instance
	 */
	private $container;

	/**
	 * Instance of the event manager.
	 *
	 * @since 3.6
	 *
	 * @var Event_Manager
	 */
	private $event_manager;

	/**
	 * Flag for if the license key is valid.
	 *
	 * @since 3.6
	 *
	 * @var bool
	 */
	private $is_valid_key;

	/**
	 * Instance of the Options.
	 *
	 * @since 3.6
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Instance of the Options_Data.
	 *
	 * @since 3.6
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the Plugin.
	 *
	 * @since 3.0
	 *
	 * @param string    $template_path Path to the views.
	 * @param Container $container     Instance of the container.
	 */
	public function __construct( $template_path, Container $container ) {
		$this->container = $container;

		add_filter( 'rocket_container', [ $this, 'get_container' ] );

		$this->container->add( 'template_path', $template_path );
	}

	/**
	 * Returns the Rocket container instance.
	 *
	 * @return Container
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load() {
		$this->event_manager = new Event_Manager();
		$this->container->share( 'event_manager', $this->event_manager );

		$this->options_api = new Options( 'wp_rocket_' );
		$this->container->add( 'options_api', $this->options_api );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Options' );
		$this->options = $this->container->get( 'options' );

		$this->container->addServiceProvider( 'WP_Rocket\Engine\Admin\Database\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Support\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Admin\Beacon\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Cache\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\CriticalPath\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\HealthCheck\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Media\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\DeferJS\ServiceProvider' );

		$this->is_valid_key = rocket_valid_key();

		foreach ( $this->get_subscribers() as $subscriber ) {
			$this->event_manager->add_subscriber( $this->container->get( $subscriber ) );
		}
	}

	/**
	 * Get the subscribers to add to the event manager.
	 *
	 * @since 3.6
	 *
	 * @return array array of subscribers.
	 */
	private function get_subscribers() {
		if ( is_admin() ) {
			$subscribers = $this->init_admin_subscribers();
		} elseif ( $this->is_valid_key ) {
			$subscribers = $this->init_valid_key_subscribers();
		} else {
			$subscribers = [];
		}

		return array_merge( $subscribers, $this->init_common_subscribers() );
	}

	/**
	 * Initializes the admin subscribers.
	 *
	 * @since 3.6
	 *
	 * @return array array of subscribers.
	 */
	private function init_admin_subscribers() {
		if ( ! Imagify_Partner::has_imagify_api_key() ) {
			$imagify = new Imagify_Partner( 'wp-rocket' );
			$imagify->init();
			remove_action( 'imagify_assets_enqueued', 'imagify_dequeue_sweetalert_wprocket' );
		}

		$this->container->add(
			'settings_page_config',
			[
				'slug'       => WP_ROCKET_PLUGIN_SLUG,
				'title'      => WP_ROCKET_PLUGIN_NAME,
				'capability' => 'rocket_manage_options',
			]
		);
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Admin\Settings\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Admin\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\AdminServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\License\ServiceProvider' );

		return [
			'beacon',
			'settings_page_subscriber',
			'deactivation_intent_subscriber',
			'hummingbird_subscriber',
			'rocketcdn_admin_subscriber',
			'rocketcdn_notices_subscriber',
			'rocketcdn_data_manager_subscriber',
			'critical_css_admin_subscriber',
			'health_check',
			'minify_css_admin_subscriber',
			'admin_cache_subscriber',
			'google_fonts_admin_subscriber',
			'license_subscriber',
			'image_dimensions_admin_subscriber',
			'defer_js_admin_subscriber',
			'lazyload_admin_subscriber',
		];
	}

	/**
	 * For plugins with a valid key, initialize the subscribers.
	 *
	 * @since 3.6
	 *
	 * @return array array of subscribers.
	 */
	private function init_valid_key_subscribers() {
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\ServiceProvider' );

		$subscribers = [
			'buffer_subscriber',
			'ie_conditionals_subscriber',
			'combine_google_fonts_subscriber',
			'minify_css_subscriber',
			'minify_js_subscriber',
			'cache_dynamic_resource',
			'emojis_subscriber',
			'delay_js_subscriber',
			'image_dimensions_subscriber',
			'defer_js_subscriber',
		];

		// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
		if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
			$subscribers[] = 'lazyload_subscriber';
		}

		return $subscribers;
	}

	/**
	 * Initializes the common subscribers.
	 *
	 * @since 3.6
	 *
	 * @return array array of common subscribers.
	 */
	private function init_common_subscribers() {
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Capabilities\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Addon\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Addon\Varnish\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Preload\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Preload\Links\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\CDN\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Common_Subscribers' );
		$this->container->addServiceProvider( 'WP_Rocket\ThirdParty\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\ThirdParty\Hostings\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Updater_Subscribers' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\DelayJS\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\RUCSS\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Heartbeat\ServiceProvider' );

		$common_subscribers = [
			'cdn_subscriber',
			'critical_css_subscriber',
			'sucuri_subscriber',
			'expired_cache_purge_subscriber',
			'preload_subscriber',
			'sitemap_preload_subscriber',
			'partial_preload_subscriber',
			'fonts_preload_subscriber',
			'heartbeat_subscriber',
			'db_optimization_subscriber',
			'mobile_subscriber',
			'woocommerce_subscriber',
			'bigcommerce_subscriber',
			'syntaxhighlighter_subscriber',
			'elementor_subscriber',
			'bridge_subscriber',
			'avada_subscriber',
			'ngg_subscriber',
			'smush_subscriber',
			'cache_dir_size_check',
			'plugin_updater_common_subscriber',
			'plugin_information_subscriber',
			'plugin_updater_subscriber',
			'capabilities_subscriber',
			'varnish_subscriber',
			'rocketcdn_rest_subscriber',
			'detect_missing_tags_subscriber',
			'purge_actions_subscriber',
			'beaverbuilder_subscriber',
			'amp_subscriber',
			'rest_cpcss_subscriber',
			'simple_custom_css',
			'pdfembedder',
			'delay_js_admin_subscriber',
			'rucss_admin_subscriber',
			'rucss_frontend_subscriber',
			'divi',
			'preload_links_admin_subscriber',
			'preload_links_subscriber',
			'support_subscriber',
			'mod_pagespeed',
			'rucss_warmup_subscriber',
			'webp_subscriber',
			'imagify_webp_subscriber',
			'shortpixel_webp_subscriber',
			'ewww_webp_subscriber',
			'optimus_webp_subscriber',
			'adthrive',
			'autoptimize',
			'wp-meteor',
			'revolution_slider_subscriber',
			'wordfence_subscriber',
			'ezoic',
		];

		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			$common_subscribers[] = $host_type;
		}

		if ( $this->options->get( 'do_cloudflare', false ) ) {
			$common_subscribers[] = 'cloudflare_subscriber';
		}

		return $common_subscribers;
	}
}
