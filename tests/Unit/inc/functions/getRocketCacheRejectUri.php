<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use stdClass;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_cache_reject_uri
 * @group Functions
 * @group Options
 */
class Test_GetRocketCacheRejectUri extends TestCase {
	protected function tearDown(): void {
		unset( $GLOBALS['wp_rewrite'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldGetRocketCacheRejectUri( $config, $expected ) {
		$GLOBALS['wp_rewrite']            = new stdClass();
		$GLOBALS['wp_rewrite']->feed_base = 'feed/';

		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cache_reject_uri', [] )
			->andReturn( $config['options']['cache_reject_uri'] );


		Functions\expect( 'esc_url_raw' )->andReturnUsing( [ $this, 'sanitizeURL' ] );
		Functions\when( 'rocket_get_home_dirname' )->justReturn( $config['home_dirname'] );

		Functions\expect( 'apply_filters' )
			->once()
			->andReturn( (array) $config['filter_rocket_cache_reject_uri'] );

		$this->assertSame(
			$expected,
			get_rocket_cache_reject_uri( true )
		);
	}
	public function sanitizeURL( $url) {

		$protocols = null;
		$_context = 'db';
		$original_url = $url;

		if ( '' === $url ) {
			return $url;
		}

		$url = str_replace( ' ', '%20', ltrim( $url ) );
		$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );

		if ( '' === $url ) {
			return $url;
		}

		if ( 0 !== stripos( $url, 'mailto:' ) ) {
			$strip = array( '%0d', '%0a', '%0D', '%0A' );
			$url   = $this->_deep_replace( $strip, $url );
		}

		$url = str_replace( ';//', '://', $url );
		/*
		 * If the URL doesn't appear to contain a scheme, we presume
		 * it needs http:// prepended (unless it's a relative link
		 * starting with /, # or ?, or a PHP file).
		 */
		if ( strpos( $url, ':' ) === false && ! in_array( $url[0], array( '/', '#', '?' ), true ) &&
		     ! preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
			$url = 'http://' . $url;
		}
		$url = str_replace( array( '[', ']' ), array( '%5B', '%5D' ), $url );
		return $url;
	}


	public function _deep_replace( $search, $subject ) {
		$subject = (string) $subject;

		$count = 1;
		while ( $count ) {
			$subject = str_replace( $search, '', $subject, $count );
		}

		return $subject;
	}
}
