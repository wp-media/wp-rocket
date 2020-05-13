<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected $options;

	public function setUp() {
		$this->wp_content_dir = 'vfs://public/wordpress/wp-content';

		parent::setUp();

		$this->stubGetRocketParseUrl();

		$this->options = Mockery::mock( Options_Data::class );
		$this->options->shouldReceive( 'get' )
			->andReturnArg(1);

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'create_rocket_uniqid' )->justReturn( 'rocket_uniqid' );

		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [
			'http://en.example.org',
			'https://example.de',
		] );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'wp_basename' )->alias( function( $path, $suffix = '' ) {
			return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
		} );

		Functions\when( 'rocket_realpath' )->alias( function( $file ) {
			$wrapper = null;
			$path    = [];

			if ( false !== strpos( $file, '://' ) ) {
				list( $wrapper, $file ) = explode( '://', $file, 2 );
			}

			foreach ( explode( '/', $file ) as $part ) {
				if ( '' === $part || '.' === $part ) {
					continue;
				}

				if ( '..' !== $part ) {
					array_push( $path, $part );
				}
				elseif ( count( $path ) > 0 ) {
					array_pop( $path );
				}
			}

			$file = join( '/', $path );

			// Put the wrapper back on the target.
			if ( $wrapper !== null ) {
				return $wrapper . '://' . $file;
			}

			return $file;
		} );
		Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );
	}
}
