<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since  3.5
 */
class DataManagerSubscriber implements Subscriber_Interface {
	const CRON_EVENT = 'rocketcdn_check_subscription_status_event';

	/**
	 * RocketCDN API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * CDNOptionsManager instance.
	 *
	 * @var CDNOptionsManager
	 */
	private $cdn_options;

	/**
	 * Constructor
	 *
	 * @param APIClient         $api_client  RocketCDN API Client instance.
	 * @param CDNOptionsManager $cdn_options CDNOptionsManager instance.
	 */
	public function __construct( APIClient $api_client, CDNOptionsManager $cdn_options ) {
		$this->api_client  = $api_client;
		$this->cdn_options = $cdn_options;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'wp_ajax_save_rocketcdn_token'           => 'update_user_token',
			'wp_ajax_rocketcdn_enable'               => 'enable',
			'wp_ajax_rocketcdn_disable'              => 'disable',
			'wp_ajax_rocketcdn_process_set'          => 'set_process_status',
			'wp_ajax_rocketcdn_process_status'       => 'get_process_status',
			'wp_ajax_rocketcdn_validate_token_cname' => 'validate_token_cname',
			self::CRON_EVENT                         => 'maybe_disable_cdn',
		];
	}

	/**
	 * Updates the RocketCDN user token value
	 *
	 * @since  3.5
	 *
	 * @return void
	 */
	public function update_user_token() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error( 'unauthorized_user' );
		}

		if ( empty( $_POST['value'] ) ) {
			delete_option( 'rocketcdn_user_token' );

			wp_send_json_success( 'user_token_deleted' );
		}

		if ( ! is_string( $_POST['value'] ) ) {
			wp_send_json_error( 'invalid_token' );
		}

		$token = sanitize_key( $_POST['value'] );

		if ( 40 !== strlen( $token ) ) {
			wp_send_json_error( 'invalid_token_length' );
		}

		update_option( 'rocketcdn_user_token', $token );

		wp_send_json_success( 'user_token_saved' );
	}

	/**
	 * Ajax callback to enable RocketCDN
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function enable() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$data = [
			'process' => 'subscribe',
		];

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			$data['message'] = 'unauthorized_user';

			wp_send_json_error( $data );
		}

		if ( empty( $_POST['cdn_url'] ) ) {
			$data['message'] = 'cdn_url_empty';

			wp_send_json_error( $data );
		}

		$cdn_url = filter_var( wp_unslash( $_POST['cdn_url'] ), FILTER_VALIDATE_URL );

		if ( ! $cdn_url ) {
			$data['message'] = 'cdn_url_invalid_format';

			wp_send_json_error( $data );
		}

		$this->cdn_options->enable( esc_url_raw( $cdn_url ) );

		$subscription = $this->api_client->get_subscription_data();

		$this->schedule_subscription_check( $subscription );
		$this->delete_process();

		$data['message'] = 'rocketcdn_enabled';

		wp_send_json_success( $data );
	}

	/**
	 * AJAX callback to disable RocketCDN
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function disable() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$data = [
			'process' => 'unsubscribe',
		];

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			$data['message'] = 'unauthorized_user';

			wp_send_json_error( $data );
		}

		$this->cdn_options->disable();

		$timestamp = wp_next_scheduled( self::CRON_EVENT );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_EVENT );
		}

		$this->delete_process();

		$data['message'] = 'rocketcdn_disabled';

		wp_send_json_success( $data );
	}

	/**
	 * Delete the option tracking the RocketCDN process state
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	private function delete_process() {
		delete_option( 'rocketcdn_process' );
	}

	/**
	 * Set the RocketCDN subscription process status
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function set_process_status() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( empty( $_POST['status'] ) ) {
			return;
		}

		$status = filter_var( $_POST['status'], FILTER_VALIDATE_BOOLEAN ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Used as a boolean.

		if ( false === $status ) {
			delete_option( 'rocketcdn_process' );
			return;
		}

		update_option( 'rocketcdn_process', $status );
	}

	/**
	 * Check for RocketCDN subscription process status
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function get_process_status() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error();
		}

		if ( get_option( 'rocketcdn_process' ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Cron job to disable CDN if the subscription expired
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function maybe_disable_cdn() {
		delete_transient( 'rocketcdn_status' );

		$subscription = $this->api_client->get_subscription_data();

		if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
			$subscription = apply_filters( 'rocket_pre_get_subscription_data', $subscription );
		}

		if ( 'running' === $subscription['subscription_status'] ) {
			$this->schedule_subscription_check( $subscription );

			return;
		}

		$this->cdn_options->disable();
	}

	/**
	 * Validates and updates the token and cname from RocketCDN Iframe.
	 *
	 * @return void
	 */
	public function validate_token_cname() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$data = [];

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			$data['message'] = 'unauthorized_user';
			wp_send_json_error( $data );
		}

		if ( empty( $_POST['cdn_url'] ) || empty( $_POST['cdn_token'] ) ) {
			$data['message'] = 'cdn_values_empty';
			wp_send_json_error( $data );
		}

		$token   = sanitize_key( $_POST['cdn_token'] );
		$cdn_url = filter_var( wp_unslash( $_POST['cdn_url'] ), FILTER_VALIDATE_URL );

		if ( ! $cdn_url ) {
			$data['message'] = 'cdn_url_invalid_format';
			wp_send_json_error( $data );
		}

		if ( 40 !== strlen( $token ) ) {
			$data['message'] = 'invalid_token_length';
			wp_send_json_error( $data );
		}

		$current_token = get_option( 'rocketcdn_user_token' );
		$current_cname = $this->cdn_options->get_cdn_cnames();

		if ( ! empty( $current_token ) ) {
			$data['message'] = 'token_already_set';
			wp_send_json_error( $data );
		}

		update_option( 'rocketcdn_user_token', $token );
		$this->cdn_options->enable( esc_url_raw( $cdn_url ) );

		$data['message'] = 'token_updated_successfully';
		wp_send_json_success( $data );
	}

	/**
	 * Schedule the next cron subscription check
	 *
	 * @since 3.5
	 *
	 * @param array $subscription Array containing the subscription data.
	 * @return void
	 */
	private function schedule_subscription_check( $subscription ) {
		$timestamp = strtotime( $subscription['subscription_next_date_update'] ) + strtotime( '+2 days' );

		if ( ! wp_next_scheduled( self::CRON_EVENT ) ) {
			wp_schedule_single_event( $timestamp, self::CRON_EVENT );
		}
	}
}
