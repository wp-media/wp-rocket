<?php

namespace WP_Rocket\Engine\Optimization\GoogleFonts\Admin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

class Settings extends Abstract_Render {
	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options       WP Rocket options instance.
	 * @param Beacon       $beacon        Beacon instance.
	 * @param string       $template_path Path to template files.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, $template_path ) {
		parent::__construct( $template_path );

		$this->options = $options;
		$this->beacon  = $beacon;
	}

	/**
	 * Displays the Google Fonts Optimization section in the tools tab
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function display_google_fonts_enabler() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! apply_filters( 'pre_get_rocket_option_minify_google_fonts', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			return;
		}

		if ( $this->options->get( 'minify_google_fonts', 0 ) ) {
			return;
		}

		$data = [
			'beacon' => $this->beacon->get_suggest( 'google_fonts' ),
		];

		echo $this->generate( 'settings/enable-google-fonts', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Callback method for the AJAX request to enable Google Fonts Optimization
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function enable_google_fonts() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error();
			return;
		}

		$this->options->set( 'minify_google_fonts', 1 );
		update_option( rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ), $this->options->get_options() );

		wp_send_json_success();
	}
}
