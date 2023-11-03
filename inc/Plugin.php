<?php

namespace WP_Rocket;

use Imagify_Partner;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\API\ServiceProvider as APIServiceProvider;
use WP_Rocket\Engine\Common\ExtractCSS\ServiceProvider as CommmonExtractCSSServiceProvider;
use WP_Rocket\Engine\Media\Lazyload\CSS\ServiceProvider as LazyloadCSSServiceProvider;
use WP_Rocket\Engine\Media\Lazyload\CSS\Admin\ServiceProvider as AdminLazyloadCSSServiceProvider;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Logger\ServiceProvider as LoggerServiceProvider;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Addon\ServiceProvider as AddonServiceProvider;
use WP_Rocket\Addon\Cloudflare\ServiceProvider as CloudflareServiceProvider;
use WP_Rocket\Addon\Varnish\ServiceProvider as VarnishServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;
use WP_Rocket\Engine\Admin\Database\ServiceProvider as AdminDatabaseServiceProvider;
use WP_Rocket\Engine\Admin\ServiceProvider as EngineAdminServiceProvider;
use WP_Rocket\Engine\Admin\Settings\ServiceProvider as SettingsServiceProvider;
use WP_Rocket\Engine\Cache\ServiceProvider as CacheServiceProvider;
use WP_Rocket\Engine\Capabilities\ServiceProvider as CapabilitiesServiceProvider;
use WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider as RocketCDNServiceProvider;
use WP_Rocket\Engine\CDN\ServiceProvider as CDNServiceProvider;
use WP_Rocket\Engine\CriticalPath\ServiceProvider as CriticalPathServiceProvider;
use WP_Rocket\Engine\HealthCheck\ServiceProvider as HealthCheckServiceProvider;
use WP_Rocket\Engine\Heartbeat\ServiceProvider as HeartbeatServiceProvider;
use WP_Rocket\Engine\License\ServiceProvider as LicenseServiceProvider;
use WP_Rocket\Engine\Media\ServiceProvider as MediaServiceProvider;
use WP_Rocket\Engine\Optimization\AdminServiceProvider as OptimizationAdminServiceProvider;
use WP_Rocket\Engine\Optimization\DeferJS\ServiceProvider as DeferJSServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\ServiceProvider as DelayJSServiceProvider;
use WP_Rocket\Engine\Optimization\DynamicLists\ServiceProvider as DynamicListsServiceProvider;
use WP_Rocket\Engine\Optimization\RUCSS\ServiceProvider as RUCSSServiceProvider;
use WP_Rocket\Engine\Optimization\ServiceProvider as OptimizationServiceProvider;
use WP_Rocket\Engine\Plugin\ServiceProvider as PluginServiceProvider;
use WP_Rocket\Engine\Preload\Links\ServiceProvider as PreloadLinksServiceProvider;
use WP_Rocket\Engine\Preload\ServiceProvider as PreloadServiceProvider;
use WP_Rocket\Engine\Support\ServiceProvider as SupportServiceProvider;
use WP_Rocket\ServiceProvider\Common_Subscribers;
use WP_Rocket\ServiceProvider\Options as OptionsServiceProvider;
use WP_Rocket\ThirdParty\Hostings\ServiceProvider as HostingsServiceProvider;
use WP_Rocket\ThirdParty\ServiceProvider as ThirdPartyServiceProvider;
use WP_Rocket\ThirdParty\Themes\ServiceProvider as ThemesServiceProvider;
use WP_Rocket\Engine\Admin\DomainChange\ServiceProvider as DomainChangeServiceProvider;
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
		$this->container->addServiceProvider( OptionsServiceProvider::class );
		$this->options = $this->container->get( 'options' );

		$this->container->addServiceProvider( LoggerServiceProvider::class );

		$this->container->get( 'logger' );

		$this->container->addServiceProvider( AdminDatabaseServiceProvider::class );
		$this->container->addServiceProvider( SupportServiceProvider::class );
		$this->container->addServiceProvider( BeaconServiceProvider::class );
		$this->container->addServiceProvider( RocketCDNServiceProvider::class );
		$this->container->addServiceProvider( CacheServiceProvider::class );
		$this->container->addServiceProvider( CriticalPathServiceProvider::class );
		$this->container->addServiceProvider( HealthCheckServiceProvider::class );
		$this->container->addServiceProvider( MediaServiceProvider::class );
		$this->container->addServiceProvider( DeferJSServiceProvider::class );

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

		$this->container->addServiceProvider( SettingsServiceProvider::class );
		$this->container->addServiceProvider( EngineAdminServiceProvider::class );
		$this->container->addServiceProvider( OptimizationAdminServiceProvider::class );
		$this->container->addServiceProvider( DomainChangeServiceProvider::class );
		$this->container->addServiceProvider( AdminLazyloadCSSServiceProvider::class );

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
			'image_dimensions_admin_subscriber',
			'defer_js_admin_subscriber',
			'lazyload_admin_subscriber',
			'preload_admin_subscriber',
			'minify_admin_subscriber',
			'action_scheduler_check',
			'actionscheduler_admin_subscriber',
			'domain_change_subscriber',
			'lazyload_css_admin_subscriber',
			'post_edit_options_subscriber',
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
		$this->container->addServiceProvider( OptimizationServiceProvider::class );

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
		$this->container->addServiceProvider( CapabilitiesServiceProvider::class );
		$this->container->addServiceProvider( AddonServiceProvider::class );

		$this->container->addServiceProvider( VarnishServiceProvider::class );
		$this->container->addServiceProvider( PreloadServiceProvider::class );
		$this->container->addServiceProvider( PreloadLinksServiceProvider::class );
		$this->container->addServiceProvider( CDNServiceProvider::class );
		$this->container->addServiceProvider( Common_Subscribers::class );
		$this->container->addServiceProvider( ThirdPartyServiceProvider::class );
		$this->container->addServiceProvider( HostingsServiceProvider::class );
		$this->container->addServiceProvider( PluginServiceProvider::class );
		$this->container->addServiceProvider( DelayJSServiceProvider::class );
		$this->container->addServiceProvider( RUCSSServiceProvider::class );
		$this->container->addServiceProvider( HeartbeatServiceProvider::class );
		$this->container->addServiceProvider( DynamicListsServiceProvider::class );
		$this->container->addServiceProvider( LicenseServiceProvider::class );
		$this->container->addServiceProvider( ThemesServiceProvider::class );
		$this->container->addServiceProvider( APIServiceProvider::class );
		$this->container->addServiceProvider( CommmonExtractCSSServiceProvider::class );
		$this->container->addServiceProvider( LazyloadCSSServiceProvider::class );

		$common_subscribers = [
			'license_subscriber',
			'cdn_subscriber',
			'cdn_admin_subscriber',
			'critical_css_subscriber',
			'sucuri_subscriber',
			'common_extractcss_subscriber',
			'expired_cache_purge_subscriber',
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
			'rucss_option_subscriber',
			'rucss_frontend_subscriber',
			'rucss_cron_subscriber',
			'divi',
			'preload_subscriber',
			'preload_front_subscriber',
			'polygon',
			'preload_links_admin_subscriber',
			'preload_links_subscriber',
			'preload_cron_subscriber',
			'support_subscriber',
			'mod_pagespeed',
			'webp_subscriber',
			'webp_admin_subscriber',
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
			'thirstyaffiliates',
			'pwa',
			'yoast_seo',
			'flatsome',
			'minimalist_blogger',
			'convertplug',
			'dynamic_lists_subscriber',
			'jevelin',
			'unlimited_elements',
			'inline_related_posts',
			'jetpack',
			'rank_math_seo',
			'all_in_one_seo_pack',
			'seopress',
			'the_seo_framework',
			'wpml',
			'xstore',
			'cloudflare_plugin_subscriber',
			'cache_config',
			'uncode',
			'rocket_lazy_load',
			'cache_config',
			'the_events_calendar',
			'admin_api_subscriber',
			'perfmatters',
			'rapidload',
			'translatepress',
			'themify',
			'wpgeotargeting',
			'lazyload_css_subscriber',
			'shoptimizer',
			'weglot',
		];

		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			$common_subscribers[] = $host_type;
		}

		if ( $this->options->get( 'do_cloudflare', false ) ) {
			$this->container->addServiceProvider( CloudflareServiceProvider::class );

			$common_subscribers[] = 'cloudflare_admin_subscriber';
			$common_subscribers[] = 'cloudflare_subscriber';
		}

		return $common_subscribers;
	}
}
