<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * @runTestsInSeparateProcesses
 */
class TestExcludeDeferJS extends TestCase {
    protected function setUp() {
        parent::setUp();
        Monkey\setUp();

        global $wp_scripts;
        $wp_scripts = new \stdClass();
        $jquery = new \stdClass();
        $jquery->src = '/wp-includes/js/jquery/jquery.js';
        $wp_scripts->registered = [
            'jquery-core' => $jquery,
        ];

        require( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php' );
    }

    protected function tearDown() {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testShouldReturnExcludeDeferJSArray() {
        Functions\when( 'get_rocket_option' )->justReturn(0);

        $exclude_defer_js = [
            'gist.github.com',
            'content.jwplatform.com',
            'js.hsforms.net',
            'www.uplaunch.com',
            'google.com/recaptcha',
            'widget.reviews.co.uk',
        ];

        $this->assertSame(
            $exclude_defer_js,
            get_rocket_exclude_defer_js()
        );
    }

    public function testShouldReturnExcludeDeferJSArrayWhenSafeMode() {
        Functions\when( 'get_rocket_option' )->justReturn(1);
        Functions\when('site_url')->returnArg();
        Functions\when('rocket_clean_exclude_file')->returnArg();

        $exclude_defer_js = [
            'gist.github.com',
            'content.jwplatform.com',
            'js.hsforms.net',
            'www.uplaunch.com',
            'google.com/recaptcha',
            'widget.reviews.co.uk',
            '/wp-includes/js/jquery/jquery.js',
            'c0.wp.com/c/(?:.+)/wp-includes/js/jquery/jquery.js',
            'ajax.googleapis.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
        ];

        $this->assertSame(
            $exclude_defer_js,
            get_rocket_exclude_defer_js()
        );
    }
}
