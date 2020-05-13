<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\CDN;

/**
 * @covers \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @group  CDN
 */
class TestRewriteCSSProperties extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteCSSProperties( $original, $expected ) {
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame(
			$expected,
			$cdn->rewrite_css_properties( $original )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteCssProperties' );
	}
}
