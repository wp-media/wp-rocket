<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @runTestsInSeparateProcesses
 * @group Functions
 * @group Options
 */
class TestExcludeDeferJS extends TestCase {
    protected function setUp() {
        parent::setUp();

        require( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php' );
    }

    public function testShouldReturnExcludeDeferJSArray() {
        Functions\when( 'get_rocket_option' )->justReturn(0);

        $this->assertSame(
            $this->get_exclude_defer_js_list( false ),
            get_rocket_exclude_defer_js()
        );
    }

    public function testShouldReturnExcludeDeferJSArrayWhenSafeMode() {
        Functions\when( 'get_rocket_option' )->justReturn(1);
        Functions\when('site_url')->returnArg();
        Functions\when('rocket_clean_exclude_file')->returnArg();
        Functions\when('wp_scripts')->alias(function() {
            $wp_scripts = new \stdClass();
            $jquery = new \stdClass();
            $jquery->src = '/wp-includes/js/jquery/jquery.js';
            $wp_scripts->registered = [
                'jquery-core' => $jquery,
            ];

            return $wp_scripts;
        });

        $this->assertSame(
            $this->get_exclude_defer_js_list( true ),
            get_rocket_exclude_defer_js()
        );
    }

    public function get_exclude_defer_js_list( $defer_jquery ) {
        $exclude_defer_js = [
            'gist.github.com',
            'content.jwplatform.com',
            'js.hsforms.net',
            'www.uplaunch.com',
            'google.com/recaptcha',
            'widget.reviews.co.uk',
            'verify.authorize.net/anetseal',
            'lib/admin/assets/lib/webfont/webfont.min.js',
            'app.mailerlite.com',
        ];

        if ( $defer_jquery ) {
            $exclude_defer_js = array_merge(
                $exclude_defer_js,
                [
                    '/wp-includes/js/jquery/jquery.js',
                    'c0.wp.com/c/(?:.+)/wp-includes/js/jquery/jquery.js',
                    'ajax.googleapis.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
                    'cdnjs.cloudflare.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
                ]
            );
        }

        return $exclude_defer_js;
    }
}
