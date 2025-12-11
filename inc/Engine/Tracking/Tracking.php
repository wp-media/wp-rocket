<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights;
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

		/**
		 * Filters the tracked options.
		 *
		 * @since 3.19.2
		 *
		 * @param string[] $options Array of options that are tracked by default.
		 * @return string[] array of strings.
		 */
		$options_to_track = wpm_apply_filters_typed(
			'string[]',
			'rocket_mixpanel_tracked_options',
			[]
		);

		foreach ( $options_to_track as $option_tracked ) {
			if ( ! isset( $old_value[ $option_tracked ], $value[ $option_tracked ] ) ) {
				continue;
			}

			if ( $old_value[ $option_tracked ] === $value[ $option_tracked ] ) {
				continue;
			}

			$this->mixpanel->track(
				'Option Changed',
				[
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
			// Update the legacy option to prevent the notice from being displayed again after the opt-in is enabled.
			update_option( 'rocket_analytics_notice_displayed', 1 );
			// Set the thank-you transient to display the thank-you notice after the opt-in is enabled.
			set_transient( 'rocket_analytics_optin', 1 );
			wp_send_json_success( 'Opt-in enabled.' );
		} elseif ( '0' === $value ) {
			$this->optin->disable();
			wp_send_json_success( 'Opt-in disabled.' );
		}

		wp_send_json_error( 'Invalid value parameter.' );
	}

	/**
	 * Add opt-in status to admin scripts.
	 *
	 * @return void
	 */
	public function localize_optin_status(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// Get the license email and hash it for privacy.
		$consumer_email = $this->options->get( 'consumer_email', '' );
		$hashed_email   = ! empty( $consumer_email ) ? $this->mixpanel->hash( $consumer_email ) : '';

		wp_localize_script(
			'wpr-admin-common',
			'rocket_mixpanel_data',
			[
				'optin_enabled' => $this->optin->is_enabled() ? true : false,
				'plugin'        => 'wp rocket ' . rocket_get_constant( 'WP_ROCKET_VERSION', '' ),
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
				'user_id'       => $hashed_email,
			]
		);
	}

	/**
	 * Injects Mixpanel JavaScript SDK when opt-in is enabled.
	 *
	 * @since 3.19.2
	 * @return void
	 */
	public function inject_mixpanel_script(): void {
		// Only inject if user has capability and opt-in is enabled.
		if ( ! current_user_can( 'rocket_manage_options' ) || ! $this->optin->is_enabled() ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$this->mixpanel->add_script();
	}

	/**
	 * Track opt-in change event.
	 *
	 * @param bool $status The new opt-in status.
	 *
	 * @return void
	 */
	public function track_optin_change( $status ): void {
		$this->mixpanel->track_optin( $status );
	}

	/**
	 * Track when a URL is added in Rocket Insights
	 *
	 * @param string $url        The URL that was added for monitoring.
	 * @param string $plan       Plan name.
	 * @param int    $urls_count The current number of URLs being monitored.
	 * @param string $source     The source of the request.
	 *
	 * @return void
	 */
	public function track_rocket_insights_url_added( $url, $plan, $urls_count, $source ): void {
		if ( ! $this->optin->can_track() ) {
			return;
		}

		$this->mixpanel->track(
			'Rocket Insights Page Added',
			[
				'context'       => 'wp_plugin',
				'plan_type'     => $plan,
				'tracked_pages' => $urls_count,
				'source'        => $source,
			]
		);
	}

	/**
	 * Tracks when a performance test is completed or failed in Rocket Insights.
	 *
	 * @since 3.20
	 *
	 * @param RocketInsights $row_details Details related to the database row.
	 * @param array          $job_details Details related to the job.
	 * @param string         $plan Plan name.
	 *
	 * @return void
	 */
	public function track_rocket_insights_test( $row_details, $job_details, $plan ): void {
		if ( ! $this->optin->can_track() ) {
			return;
		}

		if ( empty( $row_details->data ) ) {
			return;
		}

		$this->mixpanel->track_direct(
			'Rocket Insights Performance Test',
			[
				'context'   => 'wp_plugin',
				'status'    => $row_details->status,
				'score'     => $row_details->score,
				'retest'    => $row_details->data['is_retest'],
				'duration'  => time() - $row_details->data['start_time'],
				'plan_type' => $plan,
				'source'    => $row_details->data['source'],
			]
		);
	}
}
