<?php
namespace WP_Rocket;

use WP_Rocket\Admin\Logs;
use WP_Rocket\Admin\Settings\Page as Settings_Page;
use WP_Rocket\Admin\Settings\Settings;
use WP_Rocket\Admin\Settings\Render as Settings_Render;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Deactivation\Deactivation_Intent;
use WP_Rocket\Admin\Deactivation\Render as Deactivation_Intent_Render;
use WP_Rocket\Subscriber\Third_Party\Plugins;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Subscriber;
use WP_Rocket\Optimization\Cache_Dynamic_Resource;
use WP_Rocket\Optimization\Remove_Query_String;

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
		$this->options       = new Options_Data( $this->options_api->get( 'settings', array() ) );
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
		$event_manager = new Event_Manager();
		$subscribers   = [];

		if ( is_admin() ) {
			$settings_page_args = [
				'slug'       => WP_ROCKET_PLUGIN_SLUG,
				'title'      => WP_ROCKET_PLUGIN_NAME,
				'capability' => apply_filters( 'rocket_capacity', 'manage_options' ),
			];

			$subscribers = [
				new Settings_Page( $settings_page_args, new Settings( $this->options ), new Settings_Render( $this->template_path . '/settings' ) ),
				new Deactivation_Intent( new Deactivation_Intent_Render( $this->template_path . '/deactivation-intent' ), $this->options_api, $this->options ),
				new Logs(),
			];
		} elseif ( \rocket_valid_key() ) {
			$subscribers = [
				new Subscriber\Optimization\IE_Conditionals_Subscriber(),
				new Subscriber\Optimization\Minify_HTML_Subscriber( $this->options ),
				new Subscriber\Optimization\Combine_Google_Fonts_Subscriber( $this->options ),
				new Subscriber\Optimization\Minify_CSS_Subscriber( $this->options ),
				new Subscriber\Optimization\Minify_JS_Subscriber( $this->options ),
				new Subscriber\Optimization\Cache_Dynamic_Resource_Subscriber( new Cache_Dynamic_Resource( $this->options, WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ) ),
				new Subscriber\Optimization\Remove_Query_String_Subscriber( new Remove_Query_String( $this->options, WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ) ),
			];
		}

		$subscribers[] = new Plugins\Ecommerce\WooCommerce_Subscriber();
		$subscribers[] = new Subscriber\Google_Tracking_Cache_Busting_Subscriber( new Busting\Busting_Factory( WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL ), $this->options );

		foreach ( $subscribers as $subscriber ) {
			$event_manager->add_subscriber( $subscriber );
		}
	}
}
