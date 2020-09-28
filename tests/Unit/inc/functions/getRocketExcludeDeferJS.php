<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_exclude_defer_js
 * @group Functions
 * @group Options
 */
class Test_GetRocketExcludeDeferJS extends TestCase {

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
            'widget.reviews.io',
			'simplybook.(.*)/v2/widget/widget.js',
			'/wp-includes/js/dist/i18n.min.js',
            '/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
            '/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
        ];

        if ( $defer_jquery ) {
            $exclude_defer_js = array_merge(
                $exclude_defer_js,
                [
                    '/wp-includes/js/jquery/jquery.js',
                    'c0.wp.com/c/(?:.+)/wp-includes/js/jquery/jquery.js',
                    'ajax.googleapis.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
                    'cdnjs.cloudflare.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
		    'code.jquery.com/jquery-.*(?:\.min|slim)?.js',
                ]
            );
        }

        return $exclude_defer_js;
    }
}
