<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use WP_Rocket\Engine\CDN\CDN;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @group  CDN
 */
class TestRewriteCSSProperties extends TestCase {

	public function testShouldRewriteCSSProperties() {
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.css' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/rewrite.css' );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame( $rewrite, $cdn->rewrite_css_properties( $original ) );
	}
}
