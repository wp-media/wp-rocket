<?php

namespace WPMedia\Cloudflare;

use stdClass;
use Exception;

/**
 * Cloudflare API Client.
 *
 * @since 1.0
 */
class APIClient {
	const CLOUDFLARE_API = 'https://api.cloudflare.com/client/v4/';

	/**
	 * Email address for API authentication.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * API key for API authentication.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Zone ID.
	 *
	 * @var string
	 */
	protected $zone_id;

	/**
	 * An array of arguments for wp_remote_get.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * HTTP headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * APIClient constructor.
	 *
	 * @since 1.0
	 *
	 * @param string $useragent The user agent for this plugin or package. For example, "wp-rocket/3.5".
	 */
	public function __construct( $useragent ) {
		$this->args    = [
			'timeout'   => 30, // Increase from default of 5 to give extra time for the plugin to process story for exporting.
			'sslverify' => true,
			'body'      => [],
		];
		$this->headers = [
			'X-Auth-Email' => '',
			'X-Auth-Key'   => '',
			'User-Agent'   => $useragent,
			'Content-type' => 'application/json',
		];
	}

	/**
	 * Sets up the API credentials.
	 *
	 * @since 1.0
	 *
	 * @param string $email   The email associated with the Cloudflare account.
	 * @param string $api_key The API key for the associated Cloudflare account.
	 * @param string $zone_id The zone ID.
	 */
	public function set_api_credentials( $email, $api_key, $zone_id ) {
		$this->email   = $email;
		$this->api_key = $api_key;
		$this->zone_id = $zone_id;

		$this->headers['X-Auth-Email'] = $email;
		$this->headers['X-Auth-Key']   = $api_key;
	}

	/**
	 * Get zone data.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_zones() {
		return $this->get( "zones/{$this->zone_id}" );
	}

	/**
	 * Get the zone's page rules.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function list_pagerules() {
		return $this->get( "zones/{$this->zone_id}/pagerules?status=active" );
	}

	/**
	 * Purges the cache.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function purge() {
		return $this->delete( "zones/{$this->zone_id}/purge_cache", [ 'purge_everything' => true ] );
	}

	/**
	 * Purges the given URLs.
	 *
	 * @since 1.0
	 *
	 * @param array|null $urls An array of URLs that should be removed from cache.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function purge_files( array $urls ) {
		return $this->delete( "zones/{$this->zone_id}/purge_cache", [ 'files' => $urls ] );
	}

	/**
	 * Changes the zone's browser cache TTL setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_browser_cache_ttl( $value ) {
		return $this->change_setting( 'browser_cache_ttl', $value );
	}

	/**
	 * Changes the zone's rocket loader setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_rocket_loader( $value ) {
		return $this->change_setting( 'rocket_loader', $value );
	}

	/**
	 * Changes the zone's minify setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_minify( $value ) {
		return $this->change_setting( 'minify', $value );
	}

	/**
	 * Changes the zone's cache level.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_cache_level( $value ) {
		return $this->change_setting( 'cache_level', $value );
	}

	/**
	 * Changes the zone's development mode.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_development_mode( $value ) {
		return $this->change_setting( 'development_mode', $value );
	}

	/**
	 * Changes the given setting.
	 *
	 * @since 1.0
	 *
	 * @param string $setting Name of the setting to change.
	 * @param string $value   New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	protected function change_setting( $setting, $value ) {
		return $this->patch( "zones/{$this->zone_id}/settings/{$setting}", [ 'value' => $value ] );
	}

	/**
	 * Gets all of the Cloudflare settings.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_settings() {
		return $this->get( "zones/{$this->zone_id}/settings" );
	}

	/**
	 * Gets Cloudflare's IPs.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_ips() {
		return $this->get( '/ips' );
	}

	/**
	 * API call method for sending requests using GET.
	 *
	 * @since 1.0
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	protected function get( $path, array $data = [] ) {
		return $this->request( $path, $data, 'get' );
	}

	/**
	 * API call method for sending requests using DELETE.
	 *
	 * @since 1.0
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	protected function delete( $path, array $data = [] ) {
		return $this->request( $path, $data, 'delete' );
	}

	/**
	 * API call method for sending requests using PATCH.
	 *
	 * @since 1.0
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	protected function patch( $path, array $data = [] ) {
		return $this->request( $path, $data, 'patch' );
	}

	/**
	 * API call method for sending requests using GET, POST, PUT, DELETE OR PATCH.
	 *
	 * @since  1.0
	 *
	 * @author James Bell <james@james-bell.co.uk> - credit for original code adapted for version 1.0.
	 * @author WP Media
	 *
	 * @param string $path   Path of the endpoint.
	 * @param array  $data   Data to be sent along with the request.
	 * @param string $method Type of method that should be used ('GET', 'DELETE', 'PATCH').
	 *
	 * @return stdClass response object.
	 * @throws AuthenticationException When email or api key are not set.
	 * @throws UnauthorizedException When Cloudflare's API returns a 401 or 403.
	 */
	protected function request( $path, array $data = [], $method = 'get' ) {
		if ( '/ips' !== $path && ! $this->is_authorized() ) {
			throw new AuthenticationException( 'Authentication information must be provided.' );
		}

		$response = $this->do_remote_request( $path, $data, $method );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			throw new Exception( __( 'Ops Cloudflare did not provide any reply. Please try again later.', 'rocket' ) );
		}

		$data = json_decode( $data );

		if ( empty( $data->success ) ) {
			$errors = [];
			foreach ( $data->errors as $error ) {
				if ( 6003 === $error->code || 9103 === $error->code ) {
					$msg = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );

					$msg .= ' ' . sprintf(
						/* translators: %1$s = opening link; %2$s = closing link */
						__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
						// translators: Documentation exists in EN, FR; use localized URL if applicable.
						'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
						'</a>'
					);

					throw new Exception( $msg );
				}
				if ( 7003 === $error->code ) {
					$msg = __( 'Incorrect Cloudflare Zone ID.', 'rocket' );

					$msg .= ' ' . sprintf(
						/* translators: %1$s = opening link; %2$s = closing link */
						__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
						// translators: Documentation exists in EN, FR; use localized URL if applicable.
						'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
						'</a>'
					);

					throw new Exception( $msg );
				}
				$errors[] = $error->message;
			}
			throw new Exception( wp_sprintf_l( '%l ', $errors ) );
		}

		return $data;
	}

	/**
	 * Checks if the email and API key for the API credentials are set.
	 *
	 * @since 1.0
	 *
	 * @return bool true if authorized; else false.
	 */
	private function is_authorized() {
		return (
			isset( $this->email, $this->api_key )
			&&
			false !== filter_var( $this->email, FILTER_VALIDATE_EMAIL )
		);
	}

	/**
	 * Does the request remote cURL request.
	 *
	 * @since 1.0
	 *
	 * @param string $path   Path of the endpoint.
	 * @param array  $data   Data to be sent along with the request.
	 * @param string $method Type of method that should be used ('GET', 'DELETE', 'PATCH').
	 *
	 * @return array curl response packet.
	 */
	private function do_remote_request( $path, array $data, $method ) {
		$this->args['method'] = isset( $method ) ? strtoupper( $method ) : 'GET';

		if ( '/ips' !== $path ) {
			$this->args['headers'] = $this->headers;
		}

		$this->args['body'] = [];

		if ( ! empty( $data ) ) {
			$this->args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( self::CLOUDFLARE_API . $path, $this->args );

		return $response;
	}
}
