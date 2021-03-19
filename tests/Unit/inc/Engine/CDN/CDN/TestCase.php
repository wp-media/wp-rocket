<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {

	protected function setUp() : void {
		parent::setUp();

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

	}
}
