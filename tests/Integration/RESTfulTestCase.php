<?php
/**
 * Test Case for all of the integration tests.
 *
 * @package WP_Rocket\Tests\Integration
 */

namespace WP_Rocket\Tests\Integration;

use WP_Rest_Request;
use WP_REST_Server;
use WP_UnitTestCase;

abstract class RESTfulTestCase extends TestCase {
	protected $server;

	/**
	 * Setup the WP REST API Server.
	 */
	public function setUp() {
		parent::setUp();
		/**
		 * @var WP_REST_Server $wp_rest_server
		 */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );
	}

	/**
	 * Runs the RESTful endpoint which invokes WordPress to run in an integrated fashion. Callback will be fired.
	 *
	 * @param array $body_params Optional. Array of body parameters.
	 *
	 * @return array a response packet.
	 */
	protected function requestDisableEndpoint( array $body_params = [] ) {
		if ( empty( $body_params ) ) {
			$body_params = [
				'email' => '',
				'key'   => '',
			];
		}

		$request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( $body_params );

		return rest_do_request( $request )->get_data();
	}
}
