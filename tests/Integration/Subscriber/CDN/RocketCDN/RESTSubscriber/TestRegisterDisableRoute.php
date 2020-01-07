<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rest_Request;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::register_disable_route
 * @group RocketCDN
 */
class TestRegisterDisableRoute extends TestCase {
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
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
    }

	/**
	 * Test should register the disable route with the WP REST API.
	 */
    public function testShouldRegisterDisableRoute() {
	    $routes = $this->server->get_routes();
        $this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/disable', $routes );
    }

	/**
	 * Test should return an error response when providing an incorrect email in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailProvided() {
		$actual = $this->requestDisableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => ''
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email',
			'data'    => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return an error response when providing an incorrect key in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectKeyProvided() {
		$actual = $this->requestDisableEndpoint(
			[
				'email' => '',
				'key'   => '0123456'
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): key',
			'data'    => [
				'status' => 400,
				'params' => [
					'key' => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return an error response when providing an incorrect email & key in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailAndKeyProvided() {
		$actual = $this->requestDisableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '0123456'
			]
		);
		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, key',
			'data'    => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
					'key' => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return success packet when the "disable" endpoint is requested.
	 */
	public function testShouldReturnSuccessWhenDisableRequest() {
		$actual = $this->requestDisableEndpoint(
			[
				'email' => '',
				'key'   => '',
			]
		);
		$expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN disabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs the RESTful endpoint which invokes WordPress to run in an integrated fashion. Callback will be fired.
	 *
	 * @param array $body_params Array of body parameters.
	 *
	 * @return array a response packet.
	 */
	protected function requestDisableEndpoint( array $body_params ) {
		$request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( $body_params );

		return rest_do_request( $request )->get_data();
	}
}
