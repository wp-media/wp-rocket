<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;
use WP_Rocket\Engine\License\API\User;

/**
 * Class to Interact with the RocketCDN API
 */
class CreateAPIClient extends AbstractSafeAPIClient {

	private $free_url;

	public function __construct( User $user ) {
		$this->free_url = $user->get_rocketcdn_free_url();
	}

	protected function get_transient_key() {
		return 'rocket_cdn_create_request';
	}

	protected function get_api_url() {
		return $this->free_url;
	}

	public function create() {
		$response = $this->send_post_request( [], true );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return $response;
	}
}
