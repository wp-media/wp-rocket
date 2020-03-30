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
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/clearCache.php';

	public function tearDown() {
		delete_option( 'elementor_css_print_method' );

		parent::tearDown();
    }

    public function testShouldDoNothingWhenNotExternal() {
        add_option( 'elementor_css_print_method', 'internal' );

        do_action( 'elementor/core/files/clear_cache' );
        do_action( 'update_option__elementor_global_css' );
        do_action( 'delete_option__elementor_global_css' );

        $this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
    }

	public function testShouldCleanRocketCacheDirectoriesWhenElementorClearCache() {
        add_option( 'elementor_css_print_method', 'external' );

		do_action( 'elementor/core/files/clear_cache' );

		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}

	public function testShouldCleanRocketCacheDirectoriesWhenElementorUpdateOption() {
        add_option( 'elementor_css_print_method', 'external' );

		do_action( 'update_option__elementor_global_css' );

		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
    }

    public function testShouldCleanRocketCacheDirectoriesWhenElementorDeleteOption() {
        add_option( 'elementor_css_print_method', 'external' );

		do_action( 'delete_option__elementor_global_css' );

		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
        $this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}
}
