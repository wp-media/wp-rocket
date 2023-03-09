<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\API;

use Exception;
use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;
use WP_Rocket\Addon\Cloudflare\Auth\CredentialsException;

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
			'headers'   => [],
		];
	}

	/**
	 * API call method for sending requests using GET.
	 *
	 * @param string $path Path of the endpoint.
	 * @param array  $data Data to be sent along with the request.
	 *
	 * @return object
	 */
	public function get( $path, array $data = [] ) {
		return $this->request( 'get', $path, $data );
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
		return $this->request( 'post', $path, $data );
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
		return $this->request( 'delete', $path, $data );
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
		return $this->request( 'patch', $path, $data );
	}

	/**
	 * API call method for sending requests
	 *
	 * @param string $method Type of method that should be used.
	 * @param string $path   Path of the endpoint.
	 * @param array  $data   Data to be sent along with the request.
	 *
	 * @return object
	 *
	 * @throws AuthenticationException When email or api key are not set.
	 * @throws UnauthorizedException When Cloudflare's API returns a 401 or 403.
	 * @throws CredentialsException
	 */
	protected function request( $method = 'get', $path, array $data = [] ) {
		try {
			if (
				'/ips' !== $path
				&&
				! $this->auth->is_valid_credentials()
			) {
				throw new AuthenticationException( 'Authentication information must be provided.' );
			}
		} catch ( CredentialsException $e ) {
			throw new CredentialsException( $e->getMessage(), 0, $e );
		}

		$response = $this->do_remote_request( $method, $path, $data );

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

					throw new UnauthorizedException( $msg );
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

					throw new UnauthorizedException( $msg );
				}

				$errors[] = $error->message;
			}

			throw new Exception( wp_sprintf_l( '%l ', $errors ) );
		}

		return $data->result;
	}

	/**
	 * Does the request remote request.
	 *
	 * @param string $method Type of method that should be used.
	 * @param string $path   Path of the endpoint.
	 * @param array  $data   Data to be sent along with the request.
	 *
	 * @return array|WP_Error
	 */
	private function do_remote_request( string $method, string $path, array $data ): array {
		$this->args['method'] = isset( $method ) ? strtoupper( $method ) : 'GET';

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
}
