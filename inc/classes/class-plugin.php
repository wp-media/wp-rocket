<?php
namespace WP_Rocket;

use WP_Rocket\Admin\Logs;
use WP_Rocket\Admin\Settings\Page as Settings_Page;
use WP_Rocket\Admin\Settings\Settings;
use WP_Rocket\Admin\Settings\Render as Settings_Render;
use WP_Rocket\Admin\Settings\Beacon;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Deactivation\Deactivation_Intent;
use WP_Rocket\Admin\Deactivation\Render as Deactivation_Intent_Render;
use WP_Rocket\Subscriber\Third_Party\Plugins;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Subscriber;
use WP_Rocket\Optimization\Cache_Dynamic_Resource;
use WP_Rocket\Optimization\CDN_Favicons;
use WP_Rocket\Optimization\Remove_Query_String;
use WP_Rocket\Preload;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Assembly class
 */
class Plugin {
	/**
	 * Instance of Options class
	 *
	 * @since 3.0
	 *
	 * @var Options instance
	 */
	private $options_api;

	/**
	 * Instance of Options_Data class
	 *
	 * @since 3.0
	 *
	 * @var Options_Data instance
	 */
	private $options;

	/**
	 * Path to the HTML templates
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param string $template_path Path to the views.
	 */
	public function __construct( $template_path ) {
		$this->options_api   = new Options( 'wp_rocket_' );
		$this->options       = new Options_Data( $this->options_api->get( 'settings', [] ) );
		$this->template_path = $template_path;
	}

	/**
	 * Loads the plugin into WordPress
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load() {
		$event_manager   = new Event_Manager();
		$preload_process = new Preload\Full_Process();
		$subscribers     = [];

		if ( is_admin() ) {
			if ( ! \Imagify_Partner::has_imagify_api_key() ) {
				$imagify = new \Imagify_Partner( 'wp-rocket' );
				$imagify->init();
				remove_action( 'imagify_assets_enqueued', 'imagify_dequeue_sweetalert_wprocket' );
			}

			$settings_page_args = [
				'slug'       => WP_ROCKET_PLUGIN_SLUG,
				'title'      => WP_ROCKET_PLUGIN_NAME,
				'capability' => apply_filters( 'rocket_capacity', 'manage_options' ),
			];

			$beacon = new Beacon( $this->options );

			$subscribers = [
				new Settings_Page( $settings_page_args, new Settings( $this->options ), new Settings_Render( $this->template_path . '/settings' ), $beacon ),
				new Deactivation_Intent( new Deactivation_Intent_Render( $this->template_path . '/deactivation-intent' ), $this->options_api, $this->options ),
				new Subscriber\Admin\Settings\Beacon_Subscriber( $beacon ),
				//new Logs(),
			];
		} elseif ( \rocket_valid_key() ) {
			$subscribers = [
				new Subscriber\Optimization\IE_Conditionals_Subscriber(),
				new Subscriber\Optimization\Minify_HTML_Subscriber( $this->options ),
				new Subscriber\Optimization\Combine_Google_Fonts_Subscriber( $this->options ),
				new Subscriber\Optimization\Minify_CSS_Subscriber( $this->options ),
				new Subscriber\Optimization\Minify_JS_Subscriber( $this->options ),
				new Subscriber\Optimization\Cache_Dynamic_Resource_Subscriber( new Cache_Dynamic_Resource( $this->options, WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ) ),
				new Subscriber\Optimization\CDN_Favicons_Subscriber( new CDN_Favicons( $this->options ) ),
				new Subscriber\Optimization\Remove_Query_String_Subscriber( new Remove_Query_String( $this->options, WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ) ),
			];
		}

		$subscribers[] = new Plugins\Mobile_Subscriber();
		$subscribers[] = new Plugins\Ecommerce\WooCommerce_Subscriber();
		$subscribers[] = new Plugins\Security\Sucuri_Subscriber( $this->options );
		$subscribers[] = new Subscriber\Facebook_Tracking_Cache_Busting_Subscriber( new Busting\Busting_Factory( WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ), $this->options );
		$subscribers[] = new Subscriber\Google_Tracking_Cache_Busting_Subscriber( new Busting\Busting_Factory( WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ), $this->options );
		$subscribers[] = new Subscriber\Heartbeat_Subscriber( $this->options );
		$subscribers[] = new Subscriber\Preload\Preload_Subscriber( new Preload\Homepage( $preload_process ), $this->options );
		$subscribers[] = new Subscriber\Preload\Sitemap_Preload_Subscriber( new Preload\Sitemap( $preload_process ), $this->options );
		$subscribers[] = new Subscriber\Preload\Partial_Preload_Subscriber( new Preload\Partial_Process(), $this->options );

		foreach ( $subscribers as $subscriber ) {
			$event_manager->add_subscriber( $subscriber );
		}
	}
}
