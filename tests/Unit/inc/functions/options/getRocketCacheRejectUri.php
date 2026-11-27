<?php

namespace WP_Rocket\Tests\Unit\inc\functions\options;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_cache_reject_uri
 *
 * @group Functions
 * @group options
 */
class Test_GetRocketCacheRejectUri extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['wp_rewrite']            = new \stdClass();
		$GLOBALS['wp_rewrite']->feed_base = 'feed';

		Functions\when( 'rocket_get_home_dirname' )->justReturn( '' );
		Functions\when( 'rocket_clean_exclude_file' )->returnArg();
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'get_rocket_option' )->justReturn( [ '/checkout/' ] );

		// What the flag is for: a listener adds the sensitive entries only when asked to show them.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $uris, $show_safe_content = true ) {
				if ( 'rocket_cache_reject_uri' !== $tag ) {
					return $uris;
				}

				return $show_safe_content ? array_merge( (array) $uris, [ '/secret-login/' ] ) : $uris;
			}
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp_rewrite'] );

		parent::tearDown();
	}

	/**
	 * Checks that the two variants do not answer for each other.
	 *
	 * The list is memoized in the function, and the redacted variant is what the preload-links script
	 * prints into the page: an answer built for the other flag would put those URLs in the markup.
	 *
	 * @return void
	 */
	public function testShouldKeepTheRedactedVariantApartFromTheFullOne() {
		$full = get_rocket_cache_reject_uri( true );

		$this->assertStringContainsString( '/secret-login/', $full );

		$redacted = get_rocket_cache_reject_uri( false, false );

		$this->assertStringNotContainsString( '/secret-login/', $redacted );
	}
}
