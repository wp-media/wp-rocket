<?php

namespace WP_Rocket\Engine\CriticalPath\Admin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;

class Settings extends Abstract_Render {
	/**
	 * Instance of the Beacon handler.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instance of CriticalCSS.
	 *
	 * @var CriticalCSS
	 */
	private $critical_css;

	/**
	 * Creates an instance of the subscriber.
	 *
	 * @param Options_Data $options       WP Rocket Options instance.
	 * @param Beacon       $beacon        Beacon instance.
	 * @param CriticalCSS  $critical_css  CriticalCSS instance.
	 * @param string       $template_path Path to the templates folder.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, CriticalCSS $critical_css, $template_path ) {
		parent::__construct( $template_path );

		$this->beacon       = $beacon;
		$this->options      = $options;
		$this->critical_css = $critical_css;
	}

	/**
	 * Display CPCSS mobile section tool admin view.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function display_cpcss_mobile_section() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// Bailout if CPCSS is not enabled & separate cache for mobiles is not enabled.
		// Or bailout if CPCSS mobile option is false.
		if (
			! (
				$this->options->get( 'async_css', 0 )
				&&
				$this->options->get( 'cache_mobile', 0 )
				&&
				$this->options->get( 'do_caching_mobile_files', 0 )
			)
			||
			$this->options->get( 'async_css_mobile', 0 )
		) {
			return;
		}

		$data = [
			'beacon' => $this->beacon->get_suggest( 'async' ),
		];

		echo $this->generate( 'activate-cpcss-mobile', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Enable CPCSS mobile.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function enable_mobile_cpcss() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) || ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			wp_send_json_error();
		}

		$this->options->set( 'async_css_mobile', 1 );
		update_option( rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ), $this->options->get_options() );

		// Start Mobile CPCSS process.
		$this->critical_css->process_handler( 'mobile' );

		wp_send_json_success();
	}

	/**
	 * Adds async_css_mobile option to WP Rocket options.
	 *
	 * @since 3.6
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_async_css_mobile_option( $options ) {
		$options = (array) $options;

		$options['async_css_mobile'] = 1;

		return $options;
	}

	/**
	 * Sets the default value of async_css_mobile to 0 when upgrading from < 3.6.
	 *
	 * @since 3.6
	 *
	 * @param string $new_version New WP Rocket version.
	 * @param string $old_version Previous WP Rocket version.
	 */
	public function set_async_css_mobile_default_value( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.6', '>' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		$options['async_css_mobile'] = 0;

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Adds async_css_mobile to the hidden settings fields.
	 *
	 * @since 3.6
	 *
	 * @param array $hidden_settings_fields An array of hidden settings fields ID.
	 *
	 * @return array
	 */
	public function add_hidden_async_css_mobile( $hidden_settings_fields ) {
		$hidden_settings_fields = (array) $hidden_settings_fields;

		$hidden_settings_fields[] = 'async_css_mobile';

		return $hidden_settings_fields;
	}
}
