<?php
namespace WP_Rocket\Tests\Unit\inc\optimization\JS\Minify;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Optimization\JS\Minify;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Optimization\JS\Minify::optimize
 * @group Optimize
 * @group MinifyJS
 */
class Test_Optimize extends FilesystemTestCase {
	private   $minify;
	protected $rootVirtualDir = 'wordpress';
	protected $structure      = [
        'wp-includes' => [
            'js' => [
                'jquery' => [
                    'jquery.js' => 'jquery',
                ],
            ],
            'css' => [
                'dashicons.min.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
            ],
        ],
        'wp-content' => [
            'cache' => [
                'busting' => [
                    '1' => [],
                ],
            ],
            'themes' => [
                'twentytwenty' => [
                    'style.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
                    'assets'    => [
                        'script.js' => 'test',
                    ]
                ]
            ],
            'plugins' => [
                'hello-dolly' => [
                    'style.css'  => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
                    'script.js' => 'test',
                ]
            ],
        ],
	];


	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'wp-content/cache/min/' )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_URL' )
			->andReturn( 'http://example.org/wp-content/cache/min/' );

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'create_rocket_uniqid' )->justReturn( 'rocket_uniqid' );

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$parsed = parse_url( $url );

			$host     = isset( $parsed['host'] ) ? strtolower( urldecode( $parsed['host'] ) ) : '';
			$path     = isset( $parsed['path'] ) ? urldecode( $parsed['path'] ) : '';
			$scheme   = isset( $parsed['scheme'] ) ? urldecode( $parsed['scheme'] ) : '';
			$query    = isset( $parsed['query'] ) ? urldecode( $parsed['query'] ) : '';
			$fragment = isset( $parsed['fragment'] ) ? urldecode( $parsed['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );

		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [
			'http://en.example.org',
			'https://example.de',
		] );
		Functions\when( 'rocket_extract_url_component' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
        } );
        Functions\when( 'rocket_clean_exclude_file' )->alias( function( $url ) {
			return parse_url( $url, PHP_URL_PATH );
        } );
        Functions\when( 'home_url' )->justReturn( 'http://example.org' );
        Functions\when( 'rocket_url_to_path' )->alias( function( $url, $hosts ) {
            $path = parse_url( $url, PHP_URL_PATH );

            return $this->filesystem->getUrl( $path );
        } );

		Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );

		Functions\when( 'rocket_realpath' )->alias( function( $file ) {
			$path = [];

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

			$prefix = 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ? '' : '/';

			return $prefix . join( '/', $path );
		} );

		$this->minify = new Minify( $this->createMock( Options_Data::class ) );
	}

	/**
	 * @dataProvider addDataProvider
	 */
    public function testShouldMinifyJS( $original, $minified ) {
        Functions\when('rocket_extract_url_component')->alias( function($url, $component ) {
            return parse_url( $url, $component );
        });

        $this->assertSame(
            $minified,
            $this->minify->optimize( $original )
        );
    }

    /**
     * @dataProvider addCDNDataProvider
     */
    public function testShouldMinifyJSAndCDN( $original, $minified ) {
        Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css', 'js' ] )
			->andReturn( [
				'123456.rocketcdn.me',
			]
        );

        Filters\expectApplied( 'rocket_js_url' )
            ->atLeast()
            ->times(1)
            ->andReturnUsing( function( $url, $original_url ) {
                return str_replace( 'http://example.org', 'https://123456.rocketcdn.me', $url );
            } );

        $this->assertSame(
            $minified,
            $this->minify->optimize( $original )
        );
	}

	/**
     * @dataProvider addCDNPathDataProvider
     */
    public function testShouldMinifyJSAndCDNPath( $original, $minified ) {
        Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css', 'js' ] )
			->andReturn( [
				'123456.rocketcdn.me',
			]
        );

        Filters\expectApplied( 'rocket_js_url' )
            ->atLeast()
            ->times(1)
            ->andReturnUsing( function( $url, $original_url ) {
                return str_replace( 'http://example.org', 'https://123456.rocketcdn.me/path/to/cdn', $url );
            } );

        $this->assertSame(
            $minified,
            $this->minify->optimize( $original )
        );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
	}

	public function addCDNDataProvider() {
		return $this->getTestData( __DIR__, 'optimize-cdn' );
	}

	public function addCDNPathDataProvider() {
		return $this->getTestData( __DIR__, 'optimize-cdn-path' );
	}
}
