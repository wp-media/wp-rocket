<?php
namespace WP_Rocket\Tests\Unit\inc\optimization\CSS\Minify;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\CSS\Minify;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Optimization\CSS\Minify::optimize
 * @group Optimize
 * @group MinifyCSS
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

		Functions\expect( 'rocket_get_constant' )
			->zeroOrMoreTimes()
			->with( 'WP_CONTENT_DIR' )
			->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/' ) );

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
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
        } );

        Functions\when( 'home_url' )->justReturn( 'http://example.org' );
        Functions\when( 'wp_basename' )->alias( function( $path, $suffix = '' ) {
			return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
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

		Filters\expectApplied( 'rocket_url_to_path' )
			->andReturnUsing( function( $file ) {
				return str_replace( '/vfs:/', 'vfs://', $file );
			} );
		$this->minify = new Minify( $this->createMock( Options_Data::class ) );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
    public function testShouldMinifyCSS( $original, $minified, $cdn_host, $cdn_url, $site_url ) {
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css' ] )
			->andReturn( $cdn_host );
		
		Filters\expectApplied( 'rocket_before_url_to_path' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url ) use ( $cdn_url, $site_url ) {
                return str_replace( $cdn_url, $site_url, $url );
            } );

        Filters\expectApplied( 'rocket_css_url' )
			->zeroOrMoreTimes()
            ->andReturnUsing( function( $url, $original_url ) use ( $cdn_url ) {
                return str_replace( 'http://example.org', $cdn_url, $url );
            } );

        $this->assertSame(
            $minified,
            $this->minify->optimize( $original )
        );
    }
}
