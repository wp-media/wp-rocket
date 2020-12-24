<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::clean_domain
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_CleanDomain extends TestCase {
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Avada/cleanDomain.php';

	public function testShouldCleanCacheWhenAvadaCacheIsCleaned() {
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
