<?php

namespace WP_Rocket\Tests\Unit\CDN\RocketCDN\APIClient;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\APIClient::updateWebsiteStatus
 * @group RocketCDN
 */
class Test_UpdateWebsiteStatus extends TestCase {
    private $client;

    protected function setUp() {
        parent::setUp();

        $this->mockCommonWpFunctions();
        $this->client = new APIClient();
    }

    public function testShouldReturnNullWhenNoSubscriptionID() {
        Functions\when( 'get_transient' )->justReturn( [] );

        $this->assertNull( $this->client->update_website_status( true ) );
    }

    public function testShouldReturnNullWhenInvalidSubscriptionID() {
        Functions\when( 'get_transient' )->justReturn( [ 'id' => 0 ] );

        $this->assertNull( $this->client->update_website_status( true ) );
    }

    public function testShouldReturnNullWhenNoUserToken() {
        Functions\when( 'get_transient' )->justReturn( [ 'id' => 52 ] );
        Functions\when( 'get_option' )->justReturn( false );

        $this->assertNull( $this->client->update_website_status( true ) );
    }

    public function testShouldSendPatchRequest() {
        Functions\when( 'get_transient' )->justReturn( [ 'id' => 52 ] );
        Functions\when( 'get_option' )->justReturn( '01234' );
        Functions\expect( 'wp_remote_request' )
            ->once()
            ->with(
                'https://rocketcdn.me/api/website/52/',
                [
                    'method'  => 'PATCH',
			        'headers' => [
				        'Authorization' => 'Token 01234',
                    ],
                    'body'    => [
                        'is_active' => true,
                    ],
                ]
            );

        $this->client->update_website_status( true );
    }
}
