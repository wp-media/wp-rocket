<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_url_to_path
 * @group Functions
 * @group Formatting
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

    public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->atLeast()
			->times( 1 )
			->with( 'WP_CONTENT_DIR' )
            ->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/' ) );

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
		$this->assertFalse( rocket_url_to_path( 'http://example.org/wp-content/themes/storefront/style.css' ) );
		$this->assertFalse( rocket_url_to_path( 'wp-content/themes/storefront/style.css' ) );
	}

	public function testShouldReturnFalseWhenNoFileAndCDN() {
        add_filter( 'pre_get_rocket_option_cdn', '__return_true' );

		$hosts = [
			'home'                => 0,
			'123456.rocketcdn.me' => 1,
		];

        $this->assertFalse( rocket_url_to_path( 'https://123456.rocketcdn.me/wp-content/themes/storefront/style.css', $hosts ) );

        remove_filter( 'pre_get_rocket_option_cdn', '__return_true' );
	}

	/**
	 * @dataProvider addDefaultDataProvider
	 */
	public function testShouldReturnFilepath( $url, $path ) {
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
        $callback = function() {
            return 'http://example.org/wordpress/wp-content';
        };
        add_filter( 'content_url', $callback );
        update_option( 'site_url', 'http://example.org/wordpress' );

		$this->assertSame(
			$path,
			rocket_url_to_path( $url )
        );

        remove_filter( 'content_url', $callback );
        update_option( 'site_url', 'http://example.org' );
	}

	public function addSubDirectoryDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-subdir' );
	}

	/**
	 * @dataProvider addCDNDataProvider
	 */
	public function testShouldReturnFilepathWhenCDNURL( $url, $path, $hosts ) {
		add_filter( 'pre_get_rocket_option_cdn', '__return_true' );

		$this->assertSame(
			$path,
			rocket_url_to_path( $url, $hosts )
        );

        remove_filter( 'pre_get_rocket_option_cdn', '__return_true' );
	}

	public function addCDNDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-cdn' );
	}

	/**
	 * @dataProvider addCDNDAndSubDirectoryDataProvider
	 */
	public function testShouldReturnFilepathWhenCDNURLAndSubDirectoryInstallation( $url, $path, $hosts ) {
        $callback = function() {
            return 'http://example.org/wordpress/wp-content';
        };

		add_filter( 'pre_get_rocket_option_cdn', '__return_true' );
        add_filter( 'content_url', $callback );
        update_option( 'site_url', 'http://example.org/wordpress' );

        $hosts = [
			'home'                => 0,
			'123456.rocketcdn.me' => 1,
		];

		$this->assertSame(
			$path,
			rocket_url_to_path( $url, $hosts )
        );

        remove_filter( 'pre_get_rocket_option_cdn', '__return_true' );
        remove_filter( 'content_url', $callback );
        update_option( 'site_url', 'http://example.org' );
	}

	public function addCDNDAndSubDirectoryDataProvider() {
        return $this->getTestData( __DIR__, 'rocket-url-to-path-cdn-subdir' );
    }
}
