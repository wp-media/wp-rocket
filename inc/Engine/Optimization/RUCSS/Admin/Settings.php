<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

class Settings {
	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instance of Beacon class.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Used CSS table.
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 * @param Beacon       $beacon Beacon instance.
	 * @param UsedCSS      $used_css Used CSS table.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, UsedCSS $used_css ) {
		$this->options  = $options;
		$this->beacon   = $beacon;
		$this->used_css = $used_css;
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
	public function add_options( $options ): array {
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
	public function is_enabled(): bool {

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
	public function sanitize_options( array $input, AdminSettings $settings ): array {
		$input['remove_unused_css']          = $settings->sanitize_checkbox( $input, 'remove_unused_css' );
		$input['remove_unused_css_safelist'] = ! empty( $input['remove_unused_css_safelist'] ) ? rocket_sanitize_textarea_field( 'remove_unused_css_safelist', $input['remove_unused_css_safelist'] ) : [];

		return $input;
	}

	/**
	 * Add Clean used CSS link to WP Rocket admin bar item
	 *
	 * @since 3.9
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_used_css_menu_item( $wp_admin_bar ) {
		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

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

		if ( $this->has_saas_error_notice() ) {
			return;
		}

		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( ! $this->used_css->exists() ) {
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
			// translators: %1$s = plugin name, %2$s = number of seconds.
			__( '%1$s: Please wait %2$s seconds. The Remove Unused CSS service is processing your pages.', 'rocket' ),
			'<strong>WP Rocket</strong>',
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

		if ( ! $this->used_css->exists() ) {
			return;
		}

		if ( $this->has_saas_error_notice() ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'rucss_success_notice', (array) $boxes, true ) ) {
			return;
		}

		$transient = get_transient( 'rocket_rucss_processing' );
		$class     = '';

		if ( false !== $transient ) {
			$class = 'hidden';
		}

		$message = sprintf(
			// translators: %1$s = plugin name, %2$s = number of URLs, %3$s = number of seconds.
			__( '%1$s: The Used CSS of your homepage has been processed. WP Rocket will continue to generate Used CSS for up to %2$s URLs per %3$s second(s).', 'rocket' ),
			'<strong>WP Rocket</strong>',
			apply_filters( 'rocket_rucss_pending_jobs_cron_rows_count', 100 ),
			apply_filters( 'rocket_rucss_pending_jobs_cron_interval', MINUTE_IN_SECONDS )
		);

		if ( ! $this->options->get( 'manual_preload', 0 ) ) {
			$message .= ' ' . sprintf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				__( 'We suggest enabling %1$sPreload%2$s for the fastest results.', 'rocket' ),
				'<a href="#preload">',
				'</a>'
			);
		}

		$beacon = $this->beacon->get_suggest( 'async_opti' );

		$message .= '<br>' . sprintf(
			// translators: %1$s = opening link tag, %2$s = closing link tag.
			__( 'To learn more about the process check our %1$sdocumentation%2$s.', 'rocket' ),
			'<a href="' . esc_url( $beacon['url'] ) . '" data-beacon-article="' . esc_attr( $beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);

		rocket_notice_html(
			[
				'message'              => $message,
				'dismissible'          => $class,
				'id'                   => 'rocket-notice-rucss-success',
				'dismiss_button'       => 'rucss_success_notice',
				'dismiss_button_class' => 'button-primary',
			]
		);
	}

	/**
	 * Checks if we can display the RUCSS notices
	 *
	 * @param bool $check_enabled check if RUCSS is enabled.
	 *
	 * @since 3.11
	 *
	 * @return bool
	 */
	private function can_display_notice( $check_enabled = true ): bool {
		$screen = get_current_screen();

		if ( ! rocket_direct_filesystem()->is_writable( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) ) ) {
			return false;
		}

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

		if ( $check_enabled && ! $this->is_enabled() ) {
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

		if ( 'local' === wp_get_environment_type() ) {
			$options['optimize_css_delivery'] = 0;
			$options['remove_unused_css']     = 0;
			$options['async_css']             = 0;
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Updates safelist items for new SaaS compatibility
	 *
	 * @since 3.11.0.2
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function update_safelist_items( $old_version ) {
		if ( version_compare( $old_version, '3.11.0.2', '>=' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if ( empty( $options['remove_unused_css_safelist'] ) ) {
			return;
		}

		foreach ( $options['remove_unused_css_safelist'] as $key => $value ) {
			if ( str_contains( $value, '.css' ) ) {
				continue;
			}

			if ( str_starts_with( $value, '(' ) ) {
				continue;
			}

			$options['remove_unused_css_safelist'][ $key ] = '(.*)' . $value;
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Display a notification on wrong license.
	 *
	 * @return void
	 */
	public function display_wrong_license_notice() {
		if ( ! $this->can_display_notice( false ) ) {
			return;
		}

		$main_message = __( "We couldn't generate the used CSS because you're using a nulled version of WP Rocket. You need an active license to use the Remove Unused CSS feature and further improve your website's performance.", 'rocket' );
		$cta_message  = sprintf(
			// translators: %1$s = promo percentage.
			__( 'Click here to get a WP Rocket single license at %1$s off!', 'rocket' ),
			'10%%'
		);

		$message = sprintf(
		// translators: %1$s = plugin name, %2$s = opening anchor tag, %3$s = closing anchor tag.
			"%1\$s: <p>$main_message</p>%2\$s$cta_message%3\$s",
			'<strong>WP Rocket</strong>',
			'<a href="https://wp-rocket.me/?add-to-cart=191&coupon_code=iamnotapirate10" class="button button-primary" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
				'id'          => 'rocket-notice-rucss-wrong-licence',
			]
		);
	}

	/**
	 * Display an error notice when the connection to the server fails
	 *
	 * @return void
	 */
	public function display_saas_error_notice() {

		if ( ! $this->has_saas_error_notice() ) {
			$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
			if ( in_array( 'rucss_saas_error_notice', (array) $boxes, true ) ) {
				unset( $boxes['rucss_saas_error_notice'] );
				update_user_meta( get_current_user_id(), 'rocket_boxes', $boxes );
			}

			return;
		}

		if ( ! $this->can_display_notice() ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'rucss_error_notice', (array) $boxes, true ) ) {
			return;
		}

		$firewall_beacon = $this->beacon->get_suggest( 'rucss_firewall_ips' );

		$main_message = sprintf(
			// translators: %1$s = <a> open tag, %2$s = </a> closing tag.
			__( 'It seems a security plugin or the server\'s firewall prevents WP Rocket from accessing the Remove Unused CSS generator. IPs listed %1$shere in our documentation%2$s should be added to your allowlists:', 'rocket' ),
			'<a href="' . esc_url( $firewall_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $firewall_beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);

		$security_message = __( '- In the security plugin, if you are using one', 'rocket' );
		$firewall_message = __( "- In the server's firewall. Your host can help you with this", 'rocket' );

		$message = "<strong>WP Rocket</strong>: $main_message<ul><li>$security_message</li><li>$firewall_message</li></ul>";

		rocket_notice_html(
			[
				'status'               => 'error',
				'message'              => $message,
				'dismissible'          => '',
				'id'                   => 'rocket-notice-rucss-error-http',
				'dismiss_button'       => 'rucss_error_notice',
				'dismiss_button_class' => 'button-primary',
			]
		);
	}

	/**
	 * Is the error notice present.
	 *
	 * @return bool
	 */
	public function has_saas_error_notice() {
		return (bool) get_transient( 'wp_rocket_rucss_errors_count' );
	}

	/**
	 * Display a notice on table missing.
	 *
	 * @return void
	 */
	public function display_no_table_notice() {

		if ( ! $this->can_display_notice() ) {
			return;
		}
		if ( $this->used_css->exists() ) {
			return;
		}

		// translators: %1$s = plugin name, %2$s = table name, %3$s = <a> open tag, %4$s = </a> closing tag.
		$main_message = esc_html__( '%1$s: Could not create the %2$s table in the database which is necessary for the Remove Unused CSS feature to work. Please reach out to %3$sour support%4$s.', 'rocket' );

		$message = sprintf(
		// translators: %1$s = plugin name, %2$s = table name, %3$s = <a> open tag, %4$s = </a> closing tag.
			$main_message,
			'<strong>WP Rocket</strong>',
			$this->used_css->get_name(),
			'<a href="' . $this->get_support_url() . '" target="_blank" rel="noopener">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
				'id'          => 'rocket-notice-rucss-missing-table',
			]
		);
	}

	/**
	 * Get support URL.
	 *
	 * @return string
	 */
	protected function get_support_url() {
		return rocket_get_external_url(
			'support',
			[
				'utm_source' => 'wp_plugin',
				'utm_medium' => 'wp_rocket',
			]
		);
	}
}
