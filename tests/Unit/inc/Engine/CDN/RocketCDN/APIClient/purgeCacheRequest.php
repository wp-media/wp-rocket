<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\APIClient;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::purge_cache_request
 * @group RocketCDN
 */
class Test_PurgeCacheRequest extends TestCase {

    public function testShouldReturnMissingIdentifierWhenNoID() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([]);
        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnMissingIdentifierWhenWrongID() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 0,
        ]);

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnUnexpectedResponseWhenIncorrectResponseCode() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 1,
        ]);
        Functions\when('get_option')->justReturn('01234');
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(404);

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an unexpected response code.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnUnexpectedResponseWhenEmptyBody() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 1,
        ]);
        Functions\when('get_option')->justReturn('01234');
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an empty response.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnUnexpectedResponseWhenMissingParameter() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 1,
        ]);
        Functions\when('get_option')->justReturn('01234');
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(
            json_encode(
                []
            )
        );

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an unexpected response.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnErrorMessageWhenSuccessFalse() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 1,
        ]);
        Functions\when('get_option')->justReturn('01234');
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(
            json_encode(
                [
                    'success' => false,
                    'message' => 'error message'
                ]
            )
        );

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: error message.',
            ],
            $client->purge_cache_request()
        );
    }

    public function testShouldReturnSuccessMessageWhenSuccessTrue() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')
			->justReturn([
            'id' => 1,
        ]);
        Functions\when('get_option')->justReturn('01234');
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(
            json_encode(
                [
                    'success' => true,
                ]
            )
        );

        $client = new APIClient();
        $this->assertSame(
            [
                'status'  => 'success',
                'message' => 'RocketCDN cache purge successful.',
            ],
            $client->purge_cache_request()
        );
    }
}
