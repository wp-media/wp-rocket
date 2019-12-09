<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 */
class TestPurgeCacheRequest extends TestCase {
    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnMissingIdentifierWhenNoID() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([]);

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnMissingIdentifierWhenWrongID() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 0,
        ]);

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnUnexpectedResponseWhenIncorrectResponseCode() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 1,
        ]);
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(404);

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an unexpected response code.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnUnexpectedResponseWhenEmptyBody() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 1,
        ]);
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an empty response.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnUnexpectedResponseWhenMissingParameter() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 1,
        ]);
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(
            json_encode(
                []
            )
        );

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: The API returned an unexpected response.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnErrorMessageWhenSuccessFalse() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 1,
        ]);
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

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'error',
                'message' => 'RocketCDN cache purge failed: error message.',
            ],
            $page->purge_cache_request()
        );
    }

    /**
     * @covers ::purge_cache_request
     * @group RocketCDN
     */
    public function testShouldReturnSuccessMessageWhenSuccessTrue() {
        $this->mockCommonWpFunctions();

        Functions\when('get_transient')->justReturn([
            'id' => 1,
        ]);
        Functions\when('wp_remote_request')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(
            json_encode(
                [
                    'success' => true,
                ]
            )
        );

        $page = new AdminPageSubscriber( 'views/settings/rocketcdn');
        $this->assertSame(
            [
                'status'  => 'success',
                'message' => 'RocketCDN cache purge successful.',
            ],
            $page->purge_cache_request()
        );
    }
}