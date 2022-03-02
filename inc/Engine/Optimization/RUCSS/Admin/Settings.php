<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;

class Settings {
	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;


	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Add the RUCSS options to the WP Rocket options array
	 *
	 * @since 3.9
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) : array {
		$options = (array) $options;

		$options['remove_unused_css']          = 0;
		$options['remove_unused_css_safelist'] = [];

		return $options;
	}

	/**
	 * Determines if Remove Unused CSS option is enabled.
	 *
	 * @since 3.9
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'remove_unused_css', 0 );
	}

	/**
	 * Sanitizes RUCSS options values when the settings form is submitted
	 *
	 * @since 3.9
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( array $input, AdminSettings $settings ) : array {
		$input['remove_unused_css']          = $settings->sanitize_checkbox( $input, 'remove_unused_css' );
		$input['remove_unused_css_safelist'] = ! empty( $input['remove_unused_css_safelist'] ) ? rocket_sanitize_textarea_field( 'remove_unused_css_safelist', $input['remove_unused_css_safelist'] ) : [];

		return $input;
	}

	/**
	 * Add Clean used CSS link to WP Rocket admin bar item
	 *
	 * @since 3.9
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_used_css_menu_item( $wp_admin_bar ) {
		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		$referer = '';
		$action  = 'rocket_clear_usedcss';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$referer     = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'clean-used-css',
				'title'  => __( 'Clear Used CSS', 'rocket' ),
				'href'   => wp_nonce_url( admin_url( "admin-post.php?action={$action}{$referer}" ), $action ),
			]
		);
	}

	/**
	 * Displays the RUCSS progressbar
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function display_progress_bar() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		echo '<div id="rucss-progressbar" data-parent="remove_unused_css" class="wpr-field wpr-field--textarea"></div>';
	}

	/**
	 * Set optimize css delivery value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_value( $field_args ): array {
		if ( 'optimize_css_delivery' !== $field_args['id'] ) {
			return $field_args;
		}

		$async_css_value         = (bool) $this->options->get( 'async_css', 0 );
		$remove_unused_css_value = (bool) $this->options->get( 'remove_unused_css', 0 );
		$field_args['value']     = ( $remove_unused_css_value || $async_css_value );

		return $field_args;
	}

	/**
	 * Set optimize css delivery method value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_method_value( $field_args ): array {
		if ( 'optimize_css_delivery_method' !== $field_args['id'] ) {
			return $field_args;
		}

		$value = '';

		if ( (bool) $this->options->get( 'async_css', 0 ) ) {
			$value = 'async_css';
		}

		if ( (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			$value = 'remove_unused_css';
		}

		$field_args['value'] = $value;

		return $field_args;
	}

	/**
	 * Displays the RUCSS currently processing notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function display_processing_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		$transient = get_transient( 'rocket_rucss_processing' );

		if ( false === $transient ) {
			return;
		}

		$current_time = time();

		if ( $transient < $current_time ) {
			return;
		}

		$remaining = $transient - $current_time;

		$message = sprintf(
			__( 'Please wait %s seconds. The Remove Unused CSS service is processing your pages.', 'rocket' ),
			'<span id="rocket-rucss-timer">' . $remaining . '</span>'
		);

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
				'id'      => 'rocket-notice-rucss-processing',
			]
		);
	}

	/**
	 * Displays the RUCSS success notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function display_success_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		$transient = get_transient( 'rocket_rucss_processing' );
		$class     = '';

		if ( false !== $transient ) {
			$class = ' hidden';
		}

		$message = sprintf(
			__( 'Your homepage has been processed. WP Rocket will continue to generate Used CSS for up to %1$s URLs per %2$s second(s). We suggest enabling Preload for the fastest results. To learn more about the process check our documentation.', 'rocket' ),
			apply_filters( 'action_scheduler_queue_runner_batch_size', 25 ),
			apply_filters( 'rocket_rucss_pending_jobs_cron_interval', MINUTE_IN_SECONDS )
		);

		rocket_notice_html(
			[
				'message'     => $message,
				'dismissible' => 'is-dismissible' . $class,
				'id'          => 'rocket-notice-rucss-success',
			]
		);
	}

	/**
	 * Checks if we can display the RUCSS notices
	 *
	 * @since 3.11
	 *
	 * @return bool
	 */
	private function can_display_notice(): bool {
		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		if ( ! $this->is_enabled() ) {
			return false;
		}

		return true;
	}

	/**
	 * Adds the notice end time to WP Rocket localize script data
	 *
	 * @since 3.11
	 *
	 * @param array $data Localize script data.
	 *
	 * @return array
	 */
	public function add_localize_script_data( $data ): array {
		if ( ! is_array( $data ) ) {
			$data = (array) $data;
		}

		if ( ! $this->is_enabled() ) {
			return $data;
		}

		$transient = get_transient( 'rocket_rucss_processing' );

		if ( false === $transient ) {
			return $data;
		}

		$data['notice_end_time'] = $transient;

		return $data;
	}

	/**
	 * Disable combine CSS option when RUCSS is enabled
	 *
	 * @since 3.11
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 */
	public function maybe_disable_combine_css( $value, $old_value ): array {
		if ( ! isset( $value['remove_unused_css'], $value['minify_concatenate_css'] ) ) {
			return $value;
		}

		if (
			0 === $value['minify_concatenate_css']
			||
			0 === $value['remove_unused_css']
		) {
			return $value;
		}

		if (
			isset( $old_value['remove_unused_css'], $old_value['minify_concatenate_css'] )
			&&
			$value['remove_unused_css'] === $old_value['remove_unused_css']
			&&
			1 === $value['remove_unused_css']
			&&
			0 === $old_value['minify_concatenate_css']
		) {
			return $value;
		}

		$value['minify_concatenate_css'] = 0;

		return $value;
	}

	/**
	 * Disables combine CSS if RUCSS is enabled when updating to 3.11
	 *
	 * @since 3.11
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.11', '>=' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if (
			isset( $options['remove_unused_css'] )
			&&
			1 === (int) $options['remove_unused_css']
		) {
			$options['minify_concatenate_css'] = 0;
		}

		update_option( 'wp_rocket_settings', $options );
	}
}
