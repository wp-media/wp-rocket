<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\Auth;

class APIKey implements AuthInterface {
	/**
	 * Cloudflare email
	 *
	 * @var string
	 */
	private $email;

	/**
	 * Cloudflare API Key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @param string $email Cloudflare email.
	 * @param string $api_key Cloudflare API key.
	 */
	public function __construct( string $email, string $api_key ) {
		$this->email   = $email;
		$this->api_key = $api_key;
	}

	/**
	 * Gets headers for Cloudflare API request
	 *
	 * @return array
	 */
	public function get_headers(): array {
		return [
			'X-Auth-Email' => $this->email,
			'X-Auth-Key'   => $this->api_key,
		];
	}

	/**
	 * Checks if the credentials are set.
	 *
	 * @throws CredentialsException
	 *
	 * @return bool true if authorized, false otherwise.
	 */
	public function is_valid_credentials(): bool {
		if (
			empty( $this->email )
			||
			empty( $this->api_key )
		) {
			throw new CredentialsException( 'cloudflare_credentials_empty' );
		}

		return (
			isset( $this->email, $this->api_key )
			&&
			false !== filter_var( $this->email, FILTER_VALIDATE_EMAIL )
		);
	}
}
