<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Optimization\MinifyCssSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Minify_CSS_Subscriber::process()
 * @group Optimization
 */
class Test_Process extends FilesystemTestCase {
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
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'wp-content/cache/min/' );

		add_filter( 'pre_get_rocket_option_minify_css', '__return_true' );
		add_filter( 'pre_get_rocket_option_minify_concatenate_css', '__return_true' );

		$key_callback = function() {
			return '123456';
		};

		$path_callback = function( $file, $url ) {
			if ( '//example.org/wp-content/themes/storefront/style.css' === $url ) {
				return 'vfs://wp-content/themes/storefront/style.css';
			}
	
			return $file;
		};

		add_filter( 'pre_get_rocket_option_minify_css_key', $key_callback );
		add_filter( 'rocket_url_to_path', $path_callback, 10, 2 );

		$html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/original.html');

		apply_filters( 'rocket_buffer', $html );

		$this->assertTrue( $this->filesystem->exists( 'cache/min/1/3e1ae3552abbc9a53ab11fc7766f5559.css' ) );
		$this->assertTrue( $this->filesystem->exists( 'cache/min/1/3e1ae3552abbc9a53ab11fc7766f5559.css.gz' ) );

		remove_filter( 'pre_get_rocket_option_minify_css', '__return_true' );
		remove_filter( 'pre_get_rocket_option_minify_concatenate_css', '__return_true' );
		remove_filter( 'pre_get_rocket_option_minify_css_key', $key_callback );
		remove_filter( 'rocket_url_to_path', $path_callback, 10, 2 );
	}
}
