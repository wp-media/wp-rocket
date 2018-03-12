<?php
namespace WP_Rocket;

use WP_Rocket\Admin\Settings\Page as Settings_Page;
use WP_Rocket\Admin\Settings\Settings;
use WP_Rocket\Admin\Settings\Render as Settings_Render;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Deactivation\Deactivation_Intent;
use WP_Rocket\Admin\Deactivation\Render as Deactivation_Intent_Render;

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
	}
}
