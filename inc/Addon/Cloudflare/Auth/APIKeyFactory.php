<?php

namespace WPMedia\Cloudflare\Auth;

use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;
use WP_Rocket\Admin\Options_Data;

class APIKeyFactory implements AuthFactoryInterface {


	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options Data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}


	/**
	 * Create a new authentication instance.
	 *
	 * @param array $data Data to inject into the  client.
	 * @return AuthInterface
	 */
	public function create( array $data = [] ): AuthInterface {

		$cf_api_key = defined( 'WP_ROCKET_CF_API_KEY' ) ? rocket_get_constant( 'WP_ROCKET_CF_API_KEY', '' ) : $this->options->get( 'cloudflare_api_key', '' );

		$email   = key_exists( 'cloudflare_email', $data ) ? $data['cloudflare_email'] : $this->options->get( 'cloudflare_email', '' );
		$api_key = key_exists( 'cloudflare_api_key', $data ) ? $data['cloudflare_api_key'] : $cf_api_key;

		return new APIKey( $email, $api_key );
	}
}
