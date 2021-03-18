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

	protected function getOptionsMock( array $map = [] ) {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		if ( empty( $map ) ) {
			$map = [
				[ 'cdn', 0, 1 ],
				[ 'cdn_cnames', [], [ 'cdn.example.org' ] ],
				[ 'cdn_reject_files', [], [] ],
				[ 'cdn_zone', [], [ 'all' ] ],
			];
		}
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		return $options;
	}
}
