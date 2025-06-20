<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;

class Tracking extends Abstract_Render {
	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Optin instance.
	 *
	 * @var Optin
	 */
	private $optin;

	/**
	 * Mixpanel Tracking instance.
	 *
	 * @var MixpanelTracking
	 */
	private $mixpanel;

	/**
	 * Constructor.
	 *
	 * @param Options_Data     $options Options Data instance.
	 * @param Optin            $optin Optin instance.
	 * @param MixpanelTracking $mixpanel Mixpanel Tracking instance.
	 * @param string           $template_path Path to the template files.
	 */
	public function __construct( Options_Data $options, Optin $optin, MixpanelTracking $mixpanel, $template_path ) {
		parent::__construct( $template_path );

		$this->options  = $options;
		$this->optin    = $optin;
		$this->mixpanel = $mixpanel;

		$this->mixpanel->identify( $this->options->get( 'consumer_email', '' ) );
	}

	/**
	 * Track option change.
	 *
	 * @param mixed $old_value The old value of the option.
	 * @param mixed $value     The new value of the option.
	 */
	public function track_option_change( $old_value, $value ) {
		if ( ! $this->optin->is_enabled() ) {
			return;
		}

		$options_to_track = [
			'auto_preload_fonts',
		];

		foreach ( $options_to_track as $option_tracked ) {
			if ( ! isset( $old_value[ $option_tracked ], $value[ $option_tracked ] ) ) {
				continue;
			}

			if ( $old_value[ $option_tracked ] === $value[ $option_tracked ] ) {
				continue;
			}

			$this->mixpanel->track(
				'WPM Option Changed',
				[
					'brand'          => 'WP Media',
					'product'        => 'WP Rocket',
					'context'        => 'wp_plugin',
					'option_name'    => $option_tracked,
					'previous_value' => $old_value[ $option_tracked ],
					'new_value'      => $value[ $option_tracked ],
				]
			);
		}
	}

	/**
	 * Migrate opt-in to new package on upgrade
	 *
	 * @param string $new_version The new version of the plugin.
	 * @param string $old_version The old version of the plugin.
	 *
	 * @return void
	 */
	public function migrate_optin( string $new_version, string $old_version ): void {
		if ( version_compare( $old_version, '3.19.1', '>=' ) ) {
			return;
		}

		if ( ! $this->options->get( 'analytics_enabled', false ) ) {
			return;
		}

		$this->optin->enable();
	}

	/**
	 * Render the opt-in section.
	 *
	 * @return void
	 */
	public function render_optin(): void {
		echo $this->generate( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'optin',
			[
				'current_value' => (int) $this->optin->is_enabled(),
			]
		);
	}

	/**
	 * Handle AJAX request to toggle opt-in.
	 *
	 * @return void
	 */
	public function ajax_toggle_optin(): void {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error( 'Missing capability' );
		}

		if ( ! isset( $_POST['value'] ) ) {
			wp_send_json_error( 'Missing value parameter' );
		}

		$value = sanitize_key( wp_unslash( $_POST['value'] ) );

		if ( '1' === $value ) {
			$this->optin->enable();
			wp_send_json_success( 'Opt-in enabled.' );
		} elseif ( '0' === $value ) {
			$this->optin->disable();
			wp_send_json_success( 'Opt-in disabled.' );
		}

		wp_send_json_error( 'Invalid value parameter.' );
	}
}
