<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_cache
 * @group Elementor
 * @group ThirdParty
 */
class Test_ClearCache extends FilesystemTestCase {
	protected $structure = [
		'min'          => [
			'1' => [
				'5c795b0e3a1884eec34a989485f863ff.js'     => '',
				'fa2965d41f1515951de523cecb81f85e.css'    => '',
			],
		],
		'wp-rocket'    => [
			'example.org' => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
			'example.org-Greg-594d03f6ae698691165999' => [
				'about' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
			],
		],
	];

	public function tearDown() {
		delete_option( 'elementor_css_print_method' );

		parent::tearDown();
    }

    public function testShouldDoNothingWhenNotExternal() {
        add_option( 'elementor_css_print_method', 'internal' );

        do_action( 'elementor/core/files/clear_cache' );
        do_action( 'update_option__elementor_global_css' );
        do_action( 'delete_option__elementor_global_css' );

        $this->assertNotNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNotNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
    }

	public function testShouldCleanRocketCacheDirectoriesWhenElementorClearCache() {
        add_option( 'elementor_css_print_method', 'external' );

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		do_action( 'elementor/core/files/clear_cache' );

		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}

	public function testShouldCleanRocketCacheDirectoriesWhenElementorUpdateOption() {
        add_option( 'elementor_css_print_method', 'external' );

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		do_action( 'update_option__elementor_global_css' );

		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
    }

    public function testShouldCleanRocketCacheDirectoriesWhenElementorDeleteOption() {
        add_option( 'elementor_css_print_method', 'external' );

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		do_action( 'delete_option__elementor_global_css' );

		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}
}
