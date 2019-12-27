<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rest_Request;

/**
 * Tests for the REST enable request
 * @group RocketCDN
 */
class TestEnableRequest extends TestCase {
    /**
     * Test we receive a successful response when providing the correct body params in the request
     */
    public function testShouldReturnSuccessWhenCorrectDataProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => '',
                'url'   => 'https://rocketcdn.me',
            ]
        );

        $response = rest_do_request( $request );
        $expected = [
			'code'    => 'success',
			'message' => __( 'RocketCDN enabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing incorrect email in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => '',
                'url'   => 'https://rocketcdn.me',
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
     * Test we receive an error response when providing incorrect key in the request
     */
    public function testShouldReturnErrorWhenIncorrectKeyProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => '0123456',
                'url'   => 'https://rocketcdn.me',
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
     * Test we receive an error response when providing incorrect url in the request
     */
    public function testShouldReturnErrorWhenIncorrectURLProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => '',
                'url'   => '',
            ]
        );

        $response = rest_do_request( $request );
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

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing incorrect email & key in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailAndKeyProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => '0123456',
                'url'   => 'https://rocketcdn.me',
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

    /**
     * Test we receive an error response when providing incorrect email & url in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailAndURLProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => '',
                'url'   => '',
            ]
        );

        $response = rest_do_request( $request );
        $expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, url',
			'data'    => [
                'status' => 400,
                'params' => [
                    'email' => 'Invalid parameter.',
                    'url' => 'Invalid parameter.',
                ],
			],
		];

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing incorrect key & url in the request
     */
    public function testShouldReturnErrorWhenIncorrectKeyAndURLProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => '',
                'key'   => '0123456',
                'url'   => '',
            ]
        );

        $response = rest_do_request( $request );
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

        $this->assertSame( $expected, $response->get_data() );
    }

    /**
     * Test we receive an error response when providing incorrect url, email & key in the request
     */
    public function testShouldReturnErrorWhenIncorrectEmailAndURLAndKeyProvided() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_header('Content-Type', 'application/x-www-form-urlencoded');
        $request->set_body_params(
            [
                'email' => 'nulled@wp-rocket.me',
                'key'   => '0123456',
                'url'   => '',
            ]
        );

        $response = rest_do_request( $request );
        $expected = [
			'code'    => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, key, url',
			'data'    => [
                'status' => 400,
                'params' => [
                    'email' => 'Invalid parameter.',
                    'key' => 'Invalid parameter.',
                    'url' => 'Invalid parameter.',
                ],
			],
		];

        $this->assertSame( $expected, $response->get_data() );
    }
}
