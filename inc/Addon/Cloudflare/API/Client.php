<?php

namespace WP_Rocket\Addon\Cloudflare\API;

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
	 * An array of arguments for wp_remote_get.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Constructor.
	 */
	public function __construct( AuthInterface $auth ) {
		$this->auth = $auth;
		$this->args = [
			'sslverify' => true,
			'body'      => [],
		];
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
			throw new Exception( __( 'Cloudflare did not provide any reply. Please try again later.', 'rocket' ) );
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
