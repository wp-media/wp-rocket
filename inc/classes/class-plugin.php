<?php
namespace WP_Rocket;

use League\Container\Container;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Admin\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Assembly class
 */
class Plugin {

	/**
	 * Instance of Container class
	 *
	 * @since 3.3
	 *
	 * @var Container instance
	 */
	private $container;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param string $template_path Path to the views.
	 */
	public function __construct( $template_path ) {
		$this->container = new Container();

		$container = $this->container;
		add_filter(
			'rocket_container',
			function() use ( $container ) {
				return $container;
			}
		);

		$this->container->add( 'template_path', $template_path );
	}

	/**
	 * Loads the plugin into WordPress
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load() {
		$this->container->share(
			'event_manager',
			function() {
				return new Event_Manager();
			}
		);
		$this->container->add(
			'options_api',
			function() {
				return new Options( 'wp_rocket_' );
			}
		);

		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Options' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Database' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Admin\Beacon\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Cache\ServiceProvider' );

		$subscribers = [];

		if ( is_admin() ) {
			if ( ! \Imagify_Partner::has_imagify_api_key() ) {
				$imagify = new \Imagify_Partner( 'wp-rocket' );
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

			$subscribers = [
				'beacon',
				'settings_page_subscriber',
				'deactivation_intent_subscriber',
				'hummingbird_subscriber',
				'rocketcdn_admin_subscriber',
				'rocketcdn_notices_subscriber',
				'rocketcdn_data_manager_subscriber',
				'health_check',
				'minify_css_admin_subscriber',
				'admin_cache_subscriber',
			];
		} elseif ( \rocket_valid_key() ) {
			$this->container->addServiceProvider( 'WP_Rocket\Engine\Optimization\ServiceProvider' );

			$subscribers = [
				'buffer_subscriber',
				'ie_conditionals_subscriber',
				'minify_html_subscriber',
				'combine_google_fonts_subscriber',
				'minify_css_subscriber',
				'minify_js_subscriber',
				'cache_dynamic_resource',
				'remove_query_string_subscriber',
				'dequeue_jquery_migrate_subscriber',
			];

			// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
			if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
				$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Lazyload' );
				$subscribers[] = 'lazyload_subscriber';
			}
		}

		$this->container->addServiceProvider( 'WP_Rocket\Addon\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\Preload\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\Engine\CDN\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Common_Subscribers' );
		$this->container->addServiceProvider( 'WP_Rocket\ThirdParty\ServiceProvider' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Hostings_Subscribers' );
		$this->container->addServiceProvider( 'WP_Rocket\ServiceProvider\Updater_Subscribers' );

		$common_subscribers = [
			'cdn_subscriber',
			'critical_css_subscriber',
			'sucuri_subscriber',
			'facebook_tracking_subscriber',
			'google_tracking_subscriber',
			'expired_cache_purge_subscriber',
			'preload_subscriber',
			'sitemap_preload_subscriber',
			'partial_preload_subscriber',
			'heartbeat_subscriber',
			'db_optimization_subscriber',
			'mobile_subscriber',
			'woocommerce_subscriber',
			'bigcommerce_subscriber',
			'pressable_subscriber',
			'litespeed_subscriber',
			'syntaxhighlighter_subscriber',
			'elementor_subscriber',
			'bridge_subscriber',
			'ngg_subscriber',
			'smush_subscriber',
			'cache_dir_size_check_subscriber',
			'plugin_updater_common_subscriber',
			'plugin_information_subscriber',
			'plugin_updater_subscriber',
			'capabilities_subscriber',
			'varnish_subscriber',
			'rocketcdn_rest_subscriber',
			'detect_missing_tags_subscriber',
			'purge_actions_subscriber',
			'amp_subscriber',
			'cloudways',
		];

		if ( get_rocket_option( 'do_cloudflare' ) ) {
			$common_subscribers[] = 'cloudflare_subscriber';
		}

		if ( rocket_valid_key() ) {
			$common_subscribers = array_merge(
				$common_subscribers,
				[
					'webp_subscriber',
					'imagify_webp_subscriber',
					'shortpixel_webp_subscriber',
					'ewww_webp_subscriber',
					'optimus_webp_subscriber',
				]
			);
		}

		$subscribers = array_merge( $subscribers, $common_subscribers );

		foreach ( $subscribers as $subscriber ) {
			$this->container->get( 'event_manager' )->add_subscriber( $this->container->get( $subscriber ) );
		}
	}
}
