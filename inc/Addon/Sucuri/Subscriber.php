<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Sucuri;

use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * URL of the API.
	 *
	 * %s is here for the other query args.
	 *
	 * @var string
	 */
	const API_URL = 'https://waf.sucuri.net/api?v2&%s';

	/**
	 * Instance of the Option_Data class.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Instance of the Option_Data class.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_clean_domain'      => 'maybe_clean_firewall_cache',
			'after_rocket_clean_post'        => 'maybe_clean_firewall_cache',
			'after_rocket_clean_term'        => 'maybe_clean_firewall_cache',
			'after_rocket_clean_user'        => 'maybe_clean_firewall_cache',
			'after_rocket_clean_home'        => 'maybe_clean_firewall_cache',
			'after_rocket_clean_files'       => 'maybe_clean_firewall_cache',
			'admin_post_rocket_purge_sucuri' => 'do_admin_post_rocket_purge_sucuri',
			'admin_notices'                  => 'maybe_print_notice',
			'rocket_cdn_helper_addons'       => 'add_cdn_helper_message',
		];
	}

	/**
	 * Clear Sucuri firewall cache.
	 *
	 * @since 3.2
	 */
	public function maybe_clean_firewall_cache() {
		static $done = false;

		if ( $done ) {
			return;
		}

		$done = true;

		if ( ! $this->options->get( 'sucury_waf_cache_sync', 0 ) ) {
			return;
		}

		$this->clean_firewall_cache();
	}

	/**
	 * Ajax callback to empty Sucury cache.
	 *
	 * @since 3.2
	 */
	public function do_admin_post_rocket_purge_sucuri() {
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_sucuri' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_purge_sucuri_cache' ) ) {
			wp_nonce_ays( '' );
		}

		$purged = $this->clean_firewall_cache();

		if ( is_wp_error( $purged ) ) {
			$purged_result = [
				'result'  => 'error',
				/* translators: %s is the error message returned by the API. */
				'message' => sprintf( __( 'Sucuri cache purge error: %s', 'rocket' ), $purged->get_error_message() ),
			];
		} else {
			$purged_result = [
				'result'  => 'success',
				'message' => __( 'The Sucuri cache is being cleared. Note that it may take up to two minutes for it to be fully flushed.', 'rocket' ),
			];
		}

		set_transient( get_current_user_id() . '_sucuri_purge_result', $purged_result );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Print an admin notice if the cache failed to be cleared.
	 *
	 * @since 3.2
	 */
	public function maybe_print_notice() {
		if ( ! current_user_can( 'rocket_purge_sucuri_cache' ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $this->options->get( 'sucury_waf_cache_sync', 0 ) ) {
			return;
		}

		$user_id = get_current_user_id();

		$notice = get_transient( $user_id . '_sucuri_purge_result' );

		if ( ! $notice ) {
			return;
		}

		delete_transient( $user_id . '_sucuri_purge_result' );

		rocket_notice_html(
			[
				'status'  => $notice['result'],
				'message' => $notice['message'],
			]
		);
	}

	/**
	 * Tell if an API key is well formatted.
	 *
	 * @since 3.2.3
	 *
	 * @param string $api_key An API key.
	 *
	 * @return array|false An array with the keys 'k' and 's' (required by the API) if valid. False otherwise.
	 */
	public static function is_api_key_valid( $api_key ) {
		if ( '' !== $api_key && preg_match( '@^(?<k>[a-z0-9]{32})/(?<s>[a-z0-9]{32})$@', $api_key, $matches ) ) {
			return $matches;
		}

		return false;
	}

	/**
	 * Clear Sucuri firewall cache.
	 *
	 * @since 3.2
	 *
	 * @return true|WP_Error True on success. A WP_Error object on failure.
	 */
	private function clean_firewall_cache() {
		$api_key = $this->get_api_key();

		if ( is_wp_error( $api_key ) ) {
			return $api_key;
		}

		$response = $this->request_api(
			[
				'a' => 'clear_cache',
				'k' => $api_key['k'],
				's' => $api_key['s'],
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		Logger::info(
			'Sucuri firewall cache cleared.',
			[
				'sucuri firewall cache',
			]
		);

		return true;
	}

	/**
	 * Get the API key.
	 *
	 * @since 3.2
	 *
	 * @return array|WP_Error An array with the keys 'k' and 's', required by the API. A WP_Error object if no key or invalid key.
	 */
	private function get_api_key() {
		$api_key = trim( $this->options->get( 'sucury_waf_api_key', '' ) );

		if ( ! $api_key ) {
			Logger::error(
				'API key was not found.',
				[
					'sucuri firewall cache',
				]
			);

			return new WP_Error( 'no_sucuri_api_key', __( 'Sucuri firewall API key was not found.', 'rocket' ) );
		}

		$matches = self::is_api_key_valid( $api_key );

		if ( ! $matches ) {
			Logger::error(
				'API key is invalid.',
				[
					'sucuri firewall cache',
				]
			);

			return new WP_Error( 'invalid_sucuri_api_key', __( 'Sucuri firewall API key is invalid.', 'rocket' ) );
		}

		return [
			'k' => $matches['k'],
			's' => $matches['s'],
		];
	}

	/**
	 * Request against the API.
	 *
	 * @since 3.2
	 *
	 * @param array $params Parameters to send.
	 *
	 * @return array|WP_Error The response data on success. A WP_Error object on failure.
	 */
	private function request_api( $params = [] ) {
		$params['time'] = time();
		$params         = $this->build_query( $params );
		$url            = sprintf( static::API_URL, $params );

		/**
		 * Filters the arguments for the Sucuri API request
		 *
		 * @since 3.3.4
		 *
		 * @param array $args Arguments for the request.
		 */
		$args = apply_filters(
			'rocket_sucuri_api_request_args',
			[
				'timeout'     => 5,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				// This filter is documented in wp-includes/class-wp-http-streams.php.
				'sslverify'   => apply_filters( 'https_ssl_verify', true ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			Logger::error(
				'Error when contacting the API.',
				[
					'sucuri firewall cache',
					'url'      => $url,
					'response' => $response->get_error_message(),
				]
			);

			// translators: %s is an error message.
			return new WP_Error( 'wp_error_sucuri_api', sprintf( __( 'Error when contacting Sucuri firewall API. Error message was: %s', 'rocket' ), $response->get_error_message() ) );
		}

		$contents = wp_remote_retrieve_body( $response );

		if ( empty( $contents ) ) {
			Logger::error(
				'Could not get a response from the API.',
				[
					'sucuri firewall cache',
					'url'      => $url,
					'response' => $response,
				]
			);

			return new WP_Error( 'sucuri_api_no_response', __( 'Could not get a response from the Sucuri firewall API.', 'rocket' ) );
		}

		$data = json_decode( $contents, true );

		if ( ! $data || ! is_array( $data ) ) {
			Logger::error(
				'Invalid response from the API.',
				[
					'sucuri firewall cache',
					'url'           => $url,
					'response_body' => $contents,
				]
			);

			return new WP_Error( 'sucuri_api_invalid_response', __( 'Got an invalid response from the Sucuri firewall API.', 'rocket' ) );
		}

		if ( empty( $data['status'] ) ) {
			Logger::error(
				'The action failed.',
				[
					'sucuri firewall cache',
					'url'           => $url,
					'response_data' => $data,
				]
			);

			if ( empty( $data['messages'] ) || ! is_array( $data['messages'] ) ) {
				return new WP_Error( 'sucuri_api_error_status', __( 'The Sucuri firewall API returned an unknown error.', 'rocket' ) );
			}

			// translators: %s is an error message.
			$message = _n( 'The Sucuri firewall API returned the following error: %s', 'The Sucuri firewall API returned the following errors: %s', count( $data['messages'] ), 'rocket' );
			$message = sprintf( $message, '<br/>' . implode( '<br/>', $data['messages'] ) );

			return new WP_Error( 'sucuri_api_error_status', $message );
		}

		return $data;
	}

	/**
	 * Add the helper message on the CDN settings.
	 *
	 * @param string[] $addons Name from the addon that requires the helper message.
	 * @return string[]
	 */
	public function add_cdn_helper_message( array $addons ): array {

		if ( ! $this->options->get( 'sucury_waf_cache_sync', false ) ) {
			return $addons;
		}

		$addons[] = 'Sucuri';
		return $addons;
	}

	/**
	 * An i18n-friendly alternative to the built-in PHP method `http_build_query()`.
	 *
	 * @param array|object $params An array or object containing properties.
	 *
	 * @return string An URL-encoded string.
	 */
	private function build_query( $params ): string {
		if ( ! $params ) {
			return '';
		}

		$params = (array) $params;

		foreach ( $params as $param => $value ) {
			$params[ $param ] = $param . '=' . rawurlencode( (string) $value );
		}

		return implode( '&', $params );
	}
}
