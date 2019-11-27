<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @runTestsInSeparateProcesses
 */
class TestRocketCheckKey extends TestCase {
    protected function setUp() {
        parent::setUp();

        require WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';

        $this->mockCommonWpFunctions();
    }

    public function testShouldReturnTrueWhenValidKey() {

        Functions\when('rocket_valid_key')->justReturn(true);
        Functions\expect('rocket_delete_licence_data_file')
            ->once();

        $this->assertTrue( rocket_check_key() );
    }

    public function testShouldReturnArrayWhenSuccessfulValidation() {
        define( 'WP_ROCKET_WEB_VALID', 'https://wp-rocket.me/valid_key.php' );
        define('WP_ROCKET_SLUG', 'wp_rocket_settings');
        Functions\when('rocket_valid_key')->justReturn(false);
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('is_wp_error')->justReturn(false);
        Functions\when('wp_remote_retrieve_body')->justReturn('{"success": true, "data":{"consumer_key":"ABCDEF","consumer_email":"example@example.org","secret_key":"secret"}}');
        Functions\when('get_rocket_option')->justReturn(true);
        Functions\when('set_transient')->justReturn(true);
        Functions\when('delete_transient')->justReturn(true);
        Functions\expect('rocket_delete_licence_data_file')
            ->once();

        $expected = [
            'consumer_key' => 'ABCDEF',
            'consumer_email' => 'example@example.org',
            'secret_key' => 'secret',
        ];

        $this->assertSame($expected, rocket_check_key());
    }

    public function testShouldReturnFalseWhenIsWPError() {
        define( 'WP_ROCKET_WEB_VALID', 'https://wp-rocket.me/valid_key.php' );
        Functions\when('rocket_valid_key')->justReturn(false);
        Functions\when('wp_remote_get')->justReturn($this->wpFaker->error());
        Functions\when('is_wp_error')->justReturn(true);
        Functions\when('set_transient')->justReturn(true);
        Functions\expect('rocket_delete_licence_data_file')
            ->never();

        $this->assertFalse(rocket_check_key());
    }

    public function testShouldReturnFalseWhenEmptyResponse() {
        define( 'WP_ROCKET_WEB_VALID', 'https://wp-rocket.me/valid_key.php' );
        Functions\when('rocket_valid_key')->justReturn(false);
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('is_wp_error')->justReturn(false);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('set_transient')->justReturn(true);
        Functions\expect('rocket_delete_licence_data_file')
            ->never();

        $this->assertFalse(rocket_check_key());
    }

    public function testShouldReturnArrayWhenSuccessFalse() {
        define( 'WP_ROCKET_WEB_VALID', 'https://wp-rocket.me/valid_key.php' );
        define('WP_ROCKET_SLUG', 'wp_rocket_settings');
        Functions\when('rocket_valid_key')->justReturn(false);
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('is_wp_error')->justReturn(false);
        Functions\when('wp_remote_retrieve_body')->justReturn('{"success": false, "data":{"consumer_key":"ABCDEF","consumer_email":"example@example.org","reason":"BAD_KEY"}}');
        Functions\when('set_transient')->justReturn(true);
        Functions\expect('rocket_delete_licence_data_file')
            ->never();

            $expected = [
                'consumer_key' => 'ABCDEF',
                'consumer_email' => 'example@example.org',
                'secret_key' => '',
            ];
    
            $this->assertSame($expected, rocket_check_key());
    }
}
