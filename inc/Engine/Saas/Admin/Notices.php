<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Notices {
	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options_Data instance.
	 * @param Beacon       $beacon Beacon instance.
	 */
	public function __construct( Options_Data $options, Beacon $beacon ) {
		$this->options = $options;
		$this->beacon  = $beacon;
	}

	/**
	 * Show admin notice after clearing SaaS tables.
	 *
	 * @return void
	 */
	public function clean_saas_result() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$response = get_transient( 'rocket_saas_clean_message' );

		if ( ! $response ) {
			return;
		}

		delete_transient( 'rocket_saas_clean_message' );

		rocket_notice_html( $response );
	}

	/**
	 * Displays the SaaS currently processing notice
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

		$transient = get_transient( 'rocket_saas_processing' );

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
			__( '%1$s: Please wait %2$s seconds. The Remove Unused CSS service is processing your pages, the plugin is optimizing LCP and the images above the fold.', 'rocket' ),
			'<strong>WP Rocket</strong>',
			'<span id="rocket-rucss-timer">' . $remaining . '</span>'
		);

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
				'id'      => 'rocket-notice-saas-processing',
			]
		);
	}

	/**
	 * Displays the SaaS success notice
	 *
	 * @return void
	 */
	public function display_success_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( $this->has_saas_error_notice() ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'saas_success_notice', (array) $boxes, true ) ) {
			return;
		}

		$transient = get_transient( 'rocket_saas_processing' );
		$class     = '';

		if ( false !== $transient || ( ! $this->options->get( 'remove_unused_css', 0 ) ) ) {
			$class = 'hidden';
		}

		$message = sprintf(
			// translators: %1$s = plugin name, %2$s = number of URLs, %3$s = number of seconds.
			__(
				'%1$s: The LCP element has been optimized, and the images above the fold were excluded from lazyload. The Used CSS of your homepage has been processed.
			 WP Rocket will continue to generate Used CSS for up to %2$s URLs per %3$s second(s).',
				'rocket'
				),
			'<strong>WP Rocket</strong>',
			rocket_apply_filter_and_deprecated(
				'rocket_saas_pending_jobs_cron_rows_count',
				[ 100 ],
				'3.16',
				'rocket_rucss_pending_jobs_cron_rows_count'
			),
			rocket_apply_filter_and_deprecated(
				'rocket_saas_pending_jobs_cron_interval',
				[ MINUTE_IN_SECONDS ],
				'3.16',
				'rocket_rucss_pending_jobs_cron_interval'
			)
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
				'id'                   => 'rocket-notice-saas-success',
				'dismiss_button'       => 'saas_success_notice',
				'dismiss_button_class' => 'button-primary',
			]
		);
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
	public function add_localize_script_data( array $data ): array {
		if ( ! $this->options->get( 'remove_unused_css', 0 ) ) {
			return $data;
		}

		$transient = get_transient( 'rocket_saas_processing' );

		if ( false === $transient ) {
			return $data;
		}

		$data['notice_end_time'] = $transient;
		$data['cron_disabled']   = rocket_get_constant( 'DISABLE_WP_CRON', false );

		return $data;
	}

	/**
	 * Display a notification on wrong license.
	 *
	 * @return void
	 */
	public function display_wrong_license_notice() {
		$transient = get_option( 'wp_rocket_no_licence' );

		if ( ! $transient ) {
			return;
		}

		if ( ! $this->can_display_notice() ) {
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

		if ( in_array( 'saas_error_notice', (array) $boxes, true ) ) {
			return;
		}

		$firewall_beacon = $this->beacon->get_suggest( 'rucss_firewall_ips' );

		$main_message = sprintf(
			// translators: %1$s = <a> open tag, %2$s = </a> closing tag.
			__( 'It seems a security plugin or the server\'s firewall prevents WP Rocket from accessing the SaaS features. IPs listed %1$shere in our documentation%2$s should be added to your allowlists:', 'rocket' ),
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
				'dismiss_button'       => 'saas_error_notice',
				'dismiss_button_class' => 'button-primary',
			]
		);
	}

	/**
	 * Checks if we can display the SaaS notices
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

		return true;
	}

	/**
	 * Is the error notice present.
	 *
	 * @return bool
	 */
	private function has_saas_error_notice() {
		return (bool) get_transient( 'wp_rocket_rucss_errors_count' );
	}
}
