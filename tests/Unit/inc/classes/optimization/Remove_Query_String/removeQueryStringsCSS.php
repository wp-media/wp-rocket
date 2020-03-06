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
class Test_removeQueryStringsCSS extends FilesystemTestCase {
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
    }

    public function addDefaultDataProvider() {
        return $this->getTestData( __DIR__, 'css/remove-query-strings' );
    }

    /**
     * @dataProvider addDefaultDataProvider
     */
    public function testShouldRemoveQueryStringsWhenCSSURL( $original, $expected ) {
        $this->assertSame(
            $expected,
            $this->rqs->remove_query_strings_css( $original )
        );
    }

    public function addCDNDataProvider() {
        return $this->getTestData( __DIR__, 'css/remove-query-strings-cdn' );
    }

    /**
     * @dataProvider addCDNDataProvider
     */
    public function testShouldRemoveQueryStringsWhenCSSURLAndCDN( $original, $expected ) {
        Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css', 'js' ] )
			->andReturn( [
				'123456.rocketcdn.me',
			]
        );

        Filters\expectApplied( 'rocket_css_url' )
            ->atLeast()
            ->times(1)
            ->andReturnUsing( function( $url, $original_url ) {
                return str_replace( 'http://example.org', 'https://123456.rocketcdn.me', $url );
            } );

        $this->assertSame(
            $expected,
            $this->rqs->remove_query_strings_css( $original )
        );
    }
}
