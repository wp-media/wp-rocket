<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_PurgeCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/BeaverBuilder/purgeCache.php';

	public function testShouldCleanRocketCacheDirectoriesWhenSaveLayout() {
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );

		do_action( 'fl_builder_before_save_layout' );

		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}

	public function testShouldCleanRocketCacheDirectoriesWhenFLCacheCleared() {
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNotNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );

		do_action( 'fl_builder_cache_cleared' );

		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}
}
