<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::register_disable_route
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::validate_email
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::validate_key
 *
 * @uses \WP_Rocket\Admin\Options_Data::get
 * @uses ::rocket_has_constant
 *
 * @group  RocketCDN
 */
class Test_RegisterDisableRoute extends ApiTestCase {

	/**
	 * Test should register the disable route with the WP REST API.
	 */
	public function testShouldRegisterRoute() {
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
				'key'   => '',
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
				'key'   => '0123456',
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
		$actual   = $this->requestDisableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '0123456',
			]
		);
		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, key',
			'data'    => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
					'key'   => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return success packet when providing the correct body params in the request.
	 */
	public function testShouldReturnSuccessWhenCorrectDataProvided() {
		$actual   = $this->requestDisableEndpoint(
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
}
