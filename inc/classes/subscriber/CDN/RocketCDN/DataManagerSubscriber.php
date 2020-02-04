<?php
namespace WP_Rocket\Subscriber\CDN\RocketCDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\CDN\RocketCDN\CDNOptionsManager;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since  3.5
 * @author Remy Perona
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
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'wp_ajax_save_rocketcdn_token'        => 'update_user_token',
			'pre_update_option_' . WP_ROCKET_SLUG => [ 'maybe_save_token', 11 ],
			'wp_ajax_rocketcdn_enable'            => 'enable',
			'wp_ajax_rocketcdn_disable'           => 'disable',
			'wp_ajax_rocketcdn_process_set'       => 'set_process_status',
			'wp_ajax_rocketcdn_process_status'    => 'get_process_status',
			self::CRON_EVENT                      => 'maybe_disable_cdn',
			'update_option_' . WP_ROCKET_SLUG     => [ 'maybe_update_api_status', 12, 2 ],
		];
	}

	/**
	 * Updates the RocketCDN user token value
	 *
	 * @since  3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function update_user_token() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( empty( $_POST['value'] ) ) {
			delete_option( 'rocketcdn_user_token' );

			wp_send_json_success( 'user_token_deleted' );

			return;
		}

		$token = sanitize_key( $_POST['value'] );

		if ( 40 !== strlen( $token ) ) {
			wp_send_json_error( 'invalid_token_length' );

			return;
		}

		update_option( 'rocketcdn_user_token', $token );

		wp_send_json_success( 'user_token_saved' );
	}

	/**
	 * Saves the RocketCDN token in the correct option if the field is filled
	 *
	 * @since  3.5
	 * @author Remy Perona
	 *
	 * @param array $value The new, unserialized option value.
	 *
	 * @return array
	 */
	public function maybe_save_token( $value ) {
		if ( empty( $value['rocketcdn_token'] ) ) {
			return $value;
		}

		$token = sanitize_text_field( $value['rocketcdn_token'] );
		unset( $value['rocketcdn_token'] );

		if ( 40 !== strlen( $token ) ) {
			add_settings_error(
				'general',
				'rocketcdn-token',
				__( 'RocketCDN token length is not 40 characters.', 'rocket' ),
				'error'
			);

			return $value;
		}

		update_option( 'rocketcdn_user_token', $token );

		return $value;
	}

	/**
	 * Ajax callback to enable RocketCDN
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function enable() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( empty( $_POST['cdn_url'] ) ) {
			wp_send_json_error( 'cdn_url_empty' );

			return;
		}

		$cdn_url = filter_var( wp_unslash( $_POST['cdn_url'] ), FILTER_VALIDATE_URL );

		if ( ! $cdn_url ) {
			wp_send_json_error( 'cdn_url_invalid_format' );

			return;
		}

		$this->cdn_options->enable( esc_url_raw( $cdn_url ) );

		$subscription = $this->api_client->get_subscription_data();

		$this->schedule_subscription_check( $subscription );
		$this->delete_process();

		wp_send_json_success( 'rocketcdn_enabled' );
	}

	/**
	 * AJAX callback to disable RocketCDN
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function disable() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$this->cdn_options->disable();

		$timestamp = wp_next_scheduled( self::CRON_EVENT );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_EVENT );
		}

		$this->delete_process();

		wp_send_json_success( 'rocketcdn_disabled' );
	}

	/**
	 * Delete the option tracking the RocketCDN process state
	 *
	 * @since 3.5
	 * @author Remy Perona
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
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function set_process_status() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( empty( $_POST['status'] ) ) {
			return;
		}

		$status = filter_var( $_POST['status'], FILTER_VALIDATE_BOOLEAN );

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
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function get_process_status() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( get_option( 'rocketcdn_process' ) ) {
			wp_send_json_success();

			return;
		}

		wp_send_json_error();
	}

	/**
	 * Cron job to disable CDN if the subscription expired
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function maybe_disable_cdn() {
		delete_transient( 'rocketcdn_status' );

		$subscription = $this->api_client->get_subscription_data();

		if ( 'running' === $subscription['subscription_status'] ) {
			$this->schedule_subscription_check( $subscription );

			return;
		}

		$this->cdn_options->disable();
	}

	/**
	 * Schedule the next cron subscription check
	 *
	 * @since 3.5
	 * @author Remy Perona
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

	/**
	 * Send request to RocketCDN API if the CDN option changed
	 *
	 * @param array $old_value Previous values for WPR options.
	 * @param array $value     New values for WPR options.
	 * @return void
	 */
	public function maybe_update_api_status( $old_value, $value ) {
		if ( ! isset( $old_value['cdn'], $value['cdn'] ) ) {
			return;
		}

		if ( $old_value['cdn'] === $value['cdn'] ) {
			return;
		}

		$this->api_client->update_website_status( (bool) $value['cdn'] );
	}
}
