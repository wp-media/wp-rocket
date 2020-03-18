<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Remove_Query_String;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\Remove_Query_String;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Optimization\Remove_Query_String::remove_query_strings_css
 * @group  Optimize
 * @group  RemoveQueryStrings
 */
class Test_RemoveQueryStringsCSS extends FilesystemTestCase {
    private $rqs;
    protected $rootVirtualDir = 'wordpress';
	protected $structure = [
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
            'cache' => [
                'busting' => [
                    '1' => [],
                ],
            ],
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
	];

	public function setUp() {
		parent::setUp();

        Functions\expect( 'rocket_get_constant' )
			->zeroOrMoreTimes()
			->with( 'WP_CONTENT_DIR' )
			->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/' ) );

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->rqs = new Remove_Query_String(
			$this->createMock( Options_Data::class ),
			'wordpress/wp-content/cache/busting/',
			'http://example.org/wp-content/cache/busting/'
		);

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

        Functions\when( 'get_bloginfo' )->justReturn( '5.3' );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [
			'http://en.example.org',
			'https://example.de',
		] );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
        } );
        Functions\when( 'wp_basename' )->alias( function( $path, $suffix = '' ) {
			return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
		} );
        Functions\when( 'home_url' )->justReturn( 'http://example.org' );
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
        Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );
    }

    public function addDataProvider() {
        return $this->getTestData( __DIR__, 'css/remove-query-strings' );
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testShouldRemoveQueryStringsWhenCSSURL( $original, $expected, $cdn_host, $cdn_url, $site_url ) {
        Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css', 'js' ] )
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
            $expected,
            $this->rqs->remove_query_strings_css( $original )
        );
    }
}
