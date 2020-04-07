<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath;

use WP_Rest_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class RestTestCase extends FilesystemTestCase {
	/**
	 * Instance of the WordPress REST Server.
	 * @var WP_REST_Server
	 */
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
	 * @param int $post_id Post ID for which the CPCSS needs to be cleaned
	 *
	 * @return array a response packet.
	 */
	protected function requestDeleteCriticalPath( $post_id ) {
		return $this->doRestRequest( 'DELETE', [], '/wp-rocket/v1/cpcss/post/' . $post_id );
	}

	/**
	 * Does the REST request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method      REST method.
	 * @param array  $body_params Body parameters.
	 * @param string $route       Requested route.
	 *
	 * @return WP_REST_Response REST response.
	 */
	protected function doRestRequest( $method, array $body_params = [], $route ) {
		$request = new WP_Rest_Request( $method, $route );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );

		if ( ! empty( $body_params ) ) {
			$request->set_body_params( $body_params );
		}

		return rest_do_request( $request )->get_data();
	}

	/**
	 * Adds CriticalPath User capabilities.
	 */
	protected function addCriticalPathUserCapabilities() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );
		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
	}
}
