<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rest_Request;

/**
 * Tests for the REST disable request
 * @group RocketCDN
 */
class TestDisableRequest extends TestCase {
    /**
     * Test we receive a successful response when providing the correct body params in the request
     */
    public function testShouldReturnSuccessWhenCorrectDataProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => ''
            ]
        );

        $response = rest_do_request( $request );
        $expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN disabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
        ];

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing an incorrect email in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => ''
            ]
        );

        $response = rest_do_request( $request );
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

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing an incorrect key in the request
     */
    public function testShouldReturnErrorWhenIncorrectKeyProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => '0123456'
            ]
        );

        $response = rest_do_request( $request );
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

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing an incorrect email & key in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailAndKeyProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/disable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => '0123456'
            ]
        );

        $response = rest_do_request( $request );
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

        $this->assertSame( $expected, $response->get_data() );
    }
}
