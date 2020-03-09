<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_url_to_path
 * @group  Functions
 * @group  Formatting
 */
class Test_RocketUrlToPath extends FilesystemTestCase {
	protected $rootVirtualDir = 'public_html';
	protected $structure = [
        'wordpress' => [
			'wp-includes' => [
				'js' => [
					'jquery' => [
						'jquery.js' => 'jquery',
					],
				],
				'css' => [
					'dashicons.min.css' => '',
				],
			],
			'wp-content' => [
				'themes' => [
					'twentytwenty' => [
						'style.css' => 'test',
						'assets'    => [
							'script.js' => 'test',
						]
					]
				],
				'plugins' => [
					'hello-dolly' => [
						'style.css'  => 'test',
						'script.js' => 'test',
					]
				],
			],
		]
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/formatting.php';
	}

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->atLeast()
			->times( 1 )
			->with( 'WP_CONTENT_DIR' )
			->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/' ) );
		Functions\when( 'wp_basename' )->alias( function( $path, $suffix = '' ) {
			return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
		} );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = '' ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'rocket_realpath' )->alias( function( $file ) {
			$path = [];
			$file = substr( $file, 6 );

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

			return 'vfs://' . join( '/', $path );
		} );
	}

	public function testShouldReturnFalseWhenNoFile() {
		Functions\when( 'get_rocket_option' )->justReturn( false );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );

		$this->assertFalse( rocket_url_to_path( 'http://example.org/wp-content/themes/storefront/style.css' ) );
		$this->assertFalse( rocket_url_to_path( 'wp-content/themes/storefront/style.css' ) );
	}

	public function testShouldReturnFalseWhenNoFileAndCDN() {
		Functions\when( 'get_rocket_option' )->justReturn( true );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );

		$hosts = [
			'home'                => 0,
			'123456.rocketcdn.me' => 1,
		];

		$this->assertFalse( rocket_url_to_path( 'https://123456.rocketcdn.me/wp-content/themes/storefront/style.css', $hosts ) );
	}

	/**
	 * @dataProvider addDefaultDataProvider
	 */
	public function testShouldReturnFilepath( $url, $path ) {
		Functions\when( 'get_rocket_option' )->justReturn( false );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );

		$this->assertSame(
			$path,
			rocket_url_to_path( $url )
		);
	}

	public function addDefaultDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path' );
	}

	/**
	 * @dataProvider addSubDirectoryDataProvider
	 */
	public function testShouldReturnFilepathWhenSubdirectoryInstallation( $url, $path ) {
		Functions\when( 'get_rocket_option' )->justReturn( false );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wordpress/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org/wordpress' );

		$this->assertSame(
			$path,
			rocket_url_to_path( $url )
		);
	}

	public function addSubDirectoryDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-subdir' );
	}

	/**
	 * @dataProvider addCDNDataProvider
	 */
	public function testShouldReturnFilepathWhenCDNURL( $url, $path, $hosts ) {
		Functions\when( 'get_rocket_option' )->justReturn( true );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );

		$this->assertSame(
			$path,
			rocket_url_to_path( $url, $hosts )
		);
	}

	public function addCDNDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-cdn' );
	}

	/**
	 * @dataProvider addCDNDAndSubDirectoryDataProvider
	 */
	public function testShouldReturnFilepathWhenCDNURLAndSubDirectoryInstallation( $url, $path, $hosts ) {
		Functions\when( 'get_rocket_option' )->justReturn( true );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wordpress/wp-content' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org/wordpress' );

		$hosts = [
			'home'                => 0,
			'123456.rocketcdn.me' => 1,
		];

		$this->assertSame(
			$path,
			rocket_url_to_path( $url, $hosts )
		);
	}

	public function addCDNDAndSubDirectoryDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-cdn-subdir' );
    }
}
