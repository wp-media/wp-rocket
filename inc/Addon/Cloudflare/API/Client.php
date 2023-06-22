<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\API;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;

class Client {
	const CLOUDFLARE_API = 'https://api.cloudflare.com/client/v4/';

	/**
	 * Auth object
	 *
	 * @var AuthInterface
	 */
	private $auth;

	/**
	 * An array of arguments for wp_remote_request()
	 *
	 * @var mixed[]
	 */
	protected $args = [];

	/**
	 * Constructor.
	 *
	 * @param AuthInterface $auth Auth implementation.
	 */
	public function __construct( AuthInterface $auth ) {
		$this->auth = $auth;
		$this->args = [
			'sslverify' => true,
			'body'      => [],
			'headers'   => [],
		];
	}
	/**
	 * Change client auth.
	 *
	 * @param AuthInterface $auth Client auth.
	 *
	 * @return void
	 */
	public function set_auth( AuthInterface $auth ) {
		$this->auth = $auth;
	}

	/**
	 * API call method for sending requests using GET.
	 *
	 * @param string  $path Path of the endpoint.
	 * @param mixed[] $data Data to be sent along with the request.
	 *
	 * @return object
	 */
	public function get( $path, array $data = [] ) {
		return $this->request( $path, 'get', $data );
	}

	/**
	 * API call method for sending requests using POST.
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return object
	 */
	public function post( $path, array $data = [] ) {
		return $this->request( $path, 'post', $data );
	}

	/**
	 * API call method for sending requests using DELETE.
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return object
	 */
	public function delete( $path, array $data = [] ) {
		return $this->request( $path, 'delete', $data );
	}

	/**
	 * API call method for sending requests using PATCH.
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return object
	 */
	public function patch( $path, array $data = [] ) {
		return $this->request( $path, 'patch', $data );
	}

	/**
	 * API call method for sending requests
	 *
	 * @param string $path   Path of the endpoint.
	 * @param string $method Type of method that should be used.
	 * @param array  $data   Data to be sent along with the request.
	 *
	 * @return object|WP_Error
	 */
	protected function request( $path, $method = 'get', array $data = [] ) {
		if ( '/ips' !== $path ) {
			$valid = $this->auth->is_valid_credentials();

			if ( is_wp_error( $valid ) ) {
				return $valid;
			}

			if ( ! $valid ) {
				return new WP_Error( 'cloudflare_invalid_credentials', 'Cloudflare credentials are invalid.' );
			}
		}

		$response = $this->do_remote_request( $path, $method, $data );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$content = wp_remote_retrieve_body( $response );

		if ( empty( $content ) ) {
			return new WP_Error( 'cloudflare_no_reply', __( 'Cloudflare did not provide any reply. Please try again later.', 'rocket' ) );
		}

		$content = json_decode( $content );

		if ( ! is_object( $content ) ) {
			return new WP_Error( 'cloudflare_content_error', __( 'Cloudflare unexpected response', 'rocket' ) );
		}

		if ( empty( $content->success ) ) {
			return $this->set_request_error( $content );
		}

		if ( ! property_exists( $content, 'result' ) ) {
			return new WP_Error( 'cloudflare_no_reply', __( 'Missing Cloudflare result.', 'rocket' ) );
		}

		return $content->result;
	}

	/**
	 * Does the request remote request.
	 *
	 * @param string $path   Path of the endpoint.
	 * @param string $method Type of method that should be used.
	 * @param array  $data   Data to be sent along with the request.
	 *
	 * @return array|WP_Error
	 */
	private function do_remote_request( string $path, string $method = 'GET', array $data = [] ) {
		$this->args['method'] = strtoupper( $method );

		$headers = [
			'User-Agent'   => 'wp-rocket/' . rocket_get_constant( 'WP_ROCKET_VERSION' ),
			'Content-Type' => 'application/json',
		];

		if ( '/ips' !== $path ) {
			$this->args['headers'] = array_merge( $headers, $this->auth->get_headers() );
		}

		$this->args['body'] = [];

		if ( ! empty( $data ) ) {
			$this->args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( self::CLOUDFLARE_API . $path, $this->args );

		return $response;
	}

	/**
	 * Sets the WP_Error when request is not successful
	 *
	 * @param object $content Response object.
	 *
	 * @return WP_Error
	 */
	private function set_request_error( $content ) {
		$errors = [];

		foreach ( $content->errors as $error ) {
			if (
				6003 === $error->code || 9103 === $error->code ) {
				$msg = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );

				$msg .= ' ' . sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				);

				return new WP_Error( 'cloudflare_incorrect_credentials', $msg );
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

				return new WP_Error( 'cloudflare_incorrect_zone_id', $msg );
			}

			$errors[] = $error->message;
		}

		return new WP_Error( 'cloudflare_request_error', wp_sprintf_l( '%l ', $errors ) );
	}
}
