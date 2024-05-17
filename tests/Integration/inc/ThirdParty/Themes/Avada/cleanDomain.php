<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

use WP_Rocket\ThirdParty\Themes\Avada;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Avada::clean_domain
 *
 * @group Themes
 */
class Test_CleanDomain extends TestCase {

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Avada/cleanDomain.php';

	public function testShouldCleanCacheWhenAvadaCacheIsCleaned() {
		$this->subscriber = new Avada( $this->container->get( 'options' ) );

		$this->event->add_subscriber( $this->subscriber );

		$cache_exists = false;

		$this->assertSame( ! $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertSame( ! $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertSame( ! $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertSame( ! $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );

		apply_filters( 'avada_clear_dynamic_css_cache', [], [] );

		$this->assertSame( $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertSame( $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertSame( $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertSame( $cache_exists, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
	}
}
