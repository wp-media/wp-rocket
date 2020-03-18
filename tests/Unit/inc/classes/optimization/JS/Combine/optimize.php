<?php
namespace WP_Rocket\Tests\Unit\inc\optimization\JS\Combine;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use MatthiasMullie\Minify;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\Assets_Local_Cache;
use WP_Rocket\Optimization\JS\Combine;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Optimization\JS\Combine::optimize
 * @group Optimize
 * @group CombineJS
 */
class Test_Optimize extends FilesystemTestCase {
	private   $combine;
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
                'min' => [
                    '1' => [
						'c31414824a105f4f0a484a3c235b884e.js' => 'combined js',
						'0d6f19b3f50bd8bae278ac5c7e41846d.js' => 'combined js',
						'40aa0e42de6db86591cbab276ebb3586.js' => 'combined js',
					],
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

		Functions\when( 'esc_url' )->alias( function( $url ) {
			return $url;
		} );

		Functions\when('wp_scripts')->alias(function() {
            $wp_scripts = new \stdClass();
            $jquery = new \stdClass();
            $jquery->src = '/wp-includes/js/jquery/jquery.js';
            $wp_scripts->queue = [];

            return $wp_scripts;
        });

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

		$options =  $this->createMock( Options_Data::class );
		$map     = [
			[ 'exclude_inline_js', [], [] ],
			[ 'exclude_js', [], [] ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );
		$this->combine = new Combine( $options, $this->createMock( Minify\JS::class ), $this->createMock( Assets_Local_Cache::class ) );
	}

	/**
	 * @dataProvider addDataProvider
	 */
    public function testShouldMinifyJS( $original, $minified, $cdn_hosts, $cdn_url ) {
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'js' ] )
			->andReturn( $cdn_hosts );

        Filters\expectApplied( 'rocket_js_url' )
			->zeroOrMoreTimes()
            ->andReturnUsing( function( $url, $original_url ) use ( $cdn_url ) {
                return str_replace( 'http://example.org', $cdn_url, $url );
			} );

        $this->assertSame(
            $minified,
            $this->combine->optimize( $original )
        );
    }

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'combine' );
	}
}
