<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\ApiTestCase;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::register_enable_route
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::validate_email
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::validate_key
 *
 * @uses \WP_Rocket\Admin\Options_Data::get
 *
 * @group  RocketCDN
 */
class Test_RegisterEnableRoute extends ApiTestCase {

	/**
	 * Test should register the enable route with the WP REST API.
	 */
	public function testShouldRegisterRoute() {
		$this->assertArrayHasKey( '/wp-rocket/v1/rocketcdn/enable', $this->server->get_routes() );
	}

	/**
	 * Test should return an error response when providing incorrect email in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '',
				'url'   => 'https://rocketcdn.me',
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
	 * Test should return an error response when providing incorrect key in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectKeyProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => '',
				'key'   => '0123456',
				'url'   => 'https://rocketcdn.me',
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
	 * Test should return an error response when providing incorrect url in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectURLProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => '',
				'key'   => '',
				'url'   => '',
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): url',
			'data'    => [
				'status' => 400,
				'params' => [
					'url' => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return an error response when providing incorrect email & key in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailAndKeyProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '0123456',
				'url'   => 'https://rocketcdn.me',
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
	 * Test should return an error response when providing incorrect email & url in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailAndURLProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '',
				'url'   => '',
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, url',
			'data'    => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
					'url'   => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return an error response when providing incorrect key & url in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectKeyAndURLProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => '',
				'key'   => '0123456',
				'url'   => '',
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): key, url',
			'data'    => [
				'status' => 400,
				'params' => [
					'key' => 'Invalid parameter.',
					'url' => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return an error response when providing incorrect url, email & key in the request.
	 */
	public function testShouldReturnErrorWhenIncorrectEmailAndURLAndKeyProvided() {
		$actual = $this->requestEnableEndpoint(
			[
				'email' => 'nulled@wp-rocket.me',
				'key'   => '0123456',
				'url'   => '',
			]
		);

		$expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, key, url',
			'data'    => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
					'key'   => 'Invalid parameter.',
					'url'   => 'Invalid parameter.',
				],
			],
		];

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test should return success packet when providing the correct body params in the request.
	 */
	public function testShouldReturnSuccessWhenCorrectDataProvided() {
		$actual   = $this->requestEnableEndpoint(
			[
				'email' => '',
				'key'   => '',
				'url'   => 'https://rocketcdn.me',
			]
		);
		$expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN enabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		$this->assertSame( $expected, $actual );
	}
}
