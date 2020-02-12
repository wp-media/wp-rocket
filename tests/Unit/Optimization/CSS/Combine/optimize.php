<?php
namespace WP_Rocket\Tests\Unit\Optimize\CSS\Combine;

use WP_Rocket\Optimization\CSS\Combine;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Optimization\CSS\Combine::optimize()
 * @group Optimize
 */
class Test_Optimize extends FilesystemTestCase {
    protected $rootVirtualDir = 'wp-content';
    protected $structure      = [
        'cache' => [
        ],
        'themes' => [
            'storefront' => [
                'assets' => [
                    'js' => [
                        'navigation.min.js'    => 'javascript code',
                    ],
                ],
                'style.css'    => 'styling code',
            ],
        ],
    ];

    public function testShouldWriteStaticFiles() {
        Functions\when( 'create_rocket_uniqid' )->justReturn( '123456' );
        Functions\when( 'get_current_blog_id' )->justReturn( '1' );
        Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'wp-content/cache/min/' )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_URL' )
            ->andReturn( 'http://example.org/wp-content/cache/min/' );

        Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
            return parse_url( $url );
        } );
        Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
        Functions\when( 'get_rocket_i18n_uri')->justReturn( false );
        Functions\when( 'rocket_extract_url_component' )->alias( function( $url, $component ) {
            return parse_url( $url, $component );
        } );
        Functions\when( 'home_url' )->justReturn( 'http://example.org' );
        Functions\when( 'rocket_url_to_path' )->justReturn( $this->filesystem->getUrl( 'themes/storefront/style.css' ) );
        Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );

        $options = $this->createMock( \WP_Rocket\Admin\Options_Data::class );
        $map     = [
            [
                'minify_css_key',
                '',
                '123456',
            ],
            [
                'exclude_css',
                [],
                [],
            ],
        ];

        $options->method( 'get' )->will( $this->returnValueMap( $map ) );

        $minifier = $this->createMock( \MatthiasMullie\Minify\CSS::class );
        $minifier->expects( $this->once() )
            ->method( 'add' )
            ->with( 'styling code' );
        $minifier->expects( $this->once() )
            ->method( 'minify' )
            ->willReturn( 'styling code' );

        $html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/original.html');

        $combine = new Combine( $options, $minifier );

        $combine->optimize( $html );

        $this->assertTrue( $this->filesystem->exists( 'cache/min/1/6575344743068fe43285fcb80f5016f7.css' ) );
        $this->assertTrue( $this->filesystem->exists( 'cache/min/1/6575344743068fe43285fcb80f5016f7.css.gz' ) );
    }
}