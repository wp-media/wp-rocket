<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_exclude_defer_js
 * @group Functions
 * @group Options
 */
class Test_GetRocketExcludeDeferJS extends TestCase {

	public function testShouldReturnExcludeDeferJSArray() {
		$this->assertSame(
			$this->get_exclude_defer_js_list( false ),
			get_rocket_exclude_defer_js()
		);
	}

	public function testShouldReturnExcludeDeferJSArrayWhenSafeMode() {
		$options = [
			'defer_all_js'      => 1,
			'defer_all_js_safe' => 1,
		];

		update_option( 'wp_rocket_settings', $options );

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
