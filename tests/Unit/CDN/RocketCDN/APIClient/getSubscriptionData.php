<?php
namespace WP_Rocket\Tests\Unit\CDN\RocketCDN\APIClient;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\APIClient::get_subscription_data
 * @group RocketCDN
 */
class Test_GetSubscriptionData extends TestCase {
    /**
     * Default returned array
     *
     * @var array
     */
    private $default = [
        'id'                            => 0,
        'is_active'                     => false,
        'cdn_url'                       => '',
        'subscription_next_date_update' => 0,
        'subscription_status'           => 'cancelled',
    ];

    /**
     * Test should return data from the transient when it exists
     */
    public function testShouldReturnCachedArrayWhenDataInTransient() {
        Functions\expect('get_transient')
        ->once()
        ->with('rocketcdn_status')
        ->andReturn($this->default);

        $client = new APIClient();

        $this->assertSame(
            $this->default,
            $client->get_subscription_data()
        );
    }

    /**
     * Test should return default data when the RocketCDN user token doesn't exist
     */
    public function testShouldReturnDefaultArrayWhenNoUserToken() {
        Functions\when('get_transient')->justReturn(false);

        Functions\expect('get_option')
        ->once()
        ->andReturn(false);

        $client = new APIClient();

        $this->assertSame(
            $this->default,
            $client->get_subscription_data()
        );
    }

    /**
     * Test should return default array when the remote request returns a response code that is not 200
     */
    public function testShouldReturnDefaultArrayWhenResponseNot200() {
        Functions\when('get_transient')->justReturn(false);

        Functions\expect('get_option')
        ->once()
        ->andReturn('9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b');

        Functions\when('home_url')->justReturn('http://example.org');
        Functions\expect('wp_remote_get')
        ->once()
        ->with(
            'https://rocketcdn.me/api/website/search/?url=http://example.org',
            [
                'headers' => [
                    'Authorization' => 'Token 9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
                ],
            ]
        )
        ->andReturn(false);

        Functions\when('wp_remote_retrieve_response_code')->justReturn(400);
        Functions\when('set_transient')->justReturn(false);

        $client = new APIClient();

        $this->assertSame(
            $this->default,
            $client->get_subscription_data()
        );
    }

    /**
     * Test should return default array when the response body is empty
     */
    public function testShouldReturnDefaultArrayWhenReponseDataIsEmpty() {
        Functions\when('get_transient')->justReturn(false);

        Functions\expect('get_option')
        ->once()
        ->andReturn('9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b');

        Functions\when('home_url')->justReturn('http://example.org');
        Functions\expect('wp_remote_get')
        ->once()
        ->with(
            'https://rocketcdn.me/api/website/search/?url=http://example.org',
            [
                'headers' => [
                    'Authorization' => 'Token 9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
                ],
            ]
        )
        ->andReturn(false);

        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('set_transient')->justReturn(false);

        $client = new APIClient();

        $this->assertSame(
            $this->default,
            $client->get_subscription_data()
        );
    }

    /**
     * Test should return array with data from API when the call is successful
     */
    public function testShouldReturnArrayWhenSuccessful() {
        Functions\when('get_transient')->justReturn(false);

        Functions\expect('get_option')
        ->once()
        ->andReturn('9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b');

        Functions\when('home_url')->justReturn('http://example.org');
        Functions\expect('wp_remote_get')
        ->once()
        ->with(
            'https://rocketcdn.me/api/website/search/?url=http://example.org',
            [
                'headers' => [
                    'Authorization' => 'Token 9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
                ],
            ]
        )
        ->andReturn(false);

        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('{"id":1,"is_active":true,"cdn_url":"https:\/\/rocketcdn.me","subscription_next_date_update":"2020-01-01","subscription_status":"active"}');
        Functions\when('set_transient')->justReturn(false);

        $client = new APIClient();

        $this->assertSame(
            [
                'id'                            => 1,
                'is_active'                     => true,
                'cdn_url'                       => 'https://rocketcdn.me',
                'subscription_next_date_update' => '2020-01-01',
                'subscription_status'           => 'active',
            ],
            $client->get_subscription_data()
        );
    }
}
