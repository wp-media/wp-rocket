<?php
namespace WP_Rocket\Tests\Integration\Functions\Options;

use WP_Rocket\Tests\Integration\TestCase;

class TestExcludeDeferJS extends TestCase {
    public function testShouldReturnExcludeDeferJSArray() {
        $exclude_defer_js = [
            'gist.github.com',
            'content.jwplatform.com',
            'js.hsforms.net',
            'www.uplaunch.com',
            'google.com/recaptcha',
            'widget.reviews.co.uk',
            'lib/admin/assets/lib/webfont/webfont.min.js',
            'app.mailerlite.com',
        ];

        $this->assertSame(
            $exclude_defer_js,
            get_rocket_exclude_defer_js()
        );
    }

    public function testShouldReturnExcludeDeferJSArrayWhenSafeMode() {
        $options = [
            'defer_all_js' => 1,
            'defer_all_js_safe' => 1,
        ];

        update_option( 'wp_rocket_settings', $options );

        $exclude_defer_js = [
            'gist.github.com',
            'content.jwplatform.com',
            'js.hsforms.net',
            'www.uplaunch.com',
            'google.com/recaptcha',
            'widget.reviews.co.uk',
            'lib/admin/assets/lib/webfont/webfont.min.js',
            'app.mailerlite.com',
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
