<?php
namespace WP_Rocket;

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
use \Wa72\HtmlPageDom\HtmlPageCrawler;

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
	 * Event_Manager instance
	 *
	 * @since 3.1
	 *
	 * @var Event_Manager
	 */
	private $event_manager;

	/**
	 * Instance of Busting\Busting_Factory class
	 *
	 * @var Busting\Busting_Factory
	 */
	private $busting_factory;

	/**
	 * Instance of the HtmlPageCrawler
	 *
	 * @var HtmlPageCrawler;
	 */
	private $crawler;
	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param string $template_path Path to the views.
	 */
	public function __construct( $template_path ) {
		$this->event_manager   = new Event_Manager();
		$this->options_api     = new Options( 'wp_rocket_' );
		$this->options         = new Options_Data( $this->options_api->get( 'settings', array() ) );
		$this->template_path   = $template_path;
		$this->crawler         = new HtmlPageCrawler();
		$this->busting_factory = new Busting\Busting_Factory( WP_ROCKET_CACHE_BUSTING_PATH, WP_ROCKET_CACHE_BUSTING_URL );
	}

	/**
	 * Loads the plugin into WordPress
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load() {
		if ( is_admin() ) {
			$settings_page_args = [
				'slug'       => WP_ROCKET_PLUGIN_SLUG,
				'title'      => WP_ROCKET_PLUGIN_NAME,
				'capability' => apply_filters( 'rocket_capacity', 'manage_options' ),
			];

			$settings        = new Settings( $this->options );
			$settings_render = new Settings_Render( $this->template_path . '/settings' );
			Settings_Page::register( $settings_page_args, $settings, $settings_render );

			Deactivation_Intent::load( new Deactivation_Intent_Render( $this->template_path . '/deactivation-intent' ), $this->options_api, $this->options );
		}

		$subscribers = [
			new Plugins\Ecommerce\WooCommerce_Compatibility(),
		];

		foreach ( $subscribers as $subscriber ) {
			$this->event_manager->add_subscriber( $subscriber );
		}

		Subscriber\Google_Tracking_Cache_Busting_Subscriber::init( $this->busting_factory, $this->crawler, $this->options );
	}
}
