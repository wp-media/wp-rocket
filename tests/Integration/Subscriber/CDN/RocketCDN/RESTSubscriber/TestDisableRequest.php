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
    public function testShouldSuccessWhenCorrectDataProvided() {
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
}
