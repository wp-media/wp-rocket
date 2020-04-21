<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Bridge;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Bridge::maybe_clear_cache
 * @group Bridge
 * @group ThirdParty
 */
class Test_MaybeClearCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Bridge/maybeClearCache.php';

	public function setUp() {
		parent::setUp();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		$container = apply_filters( 'rocket_container', '' );
		$container->get( 'event_manager' )->add_subscriber( $container->get( 'bridge_subscriber' ) );
	}

	public function tearDown() {
		delete_option( 'wp_rocket_settings' );
		delete_option( 'qode_options_proya' );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		parent::tearDown();
	}

	public function set_stylesheet() {
		return 'bridge';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCacheWhenSettingsMatch( $old_value, $value, $settings, $expected ) {
		$minify_css_value = function() use ( $settings ) {
			return $settings['minify_css'];
		};
		$minify_js_value = function() use ( $settings ) {
			return $settings['minify_js'];
		};

		add_filter( 'pre_get_rocket_option_minify_css', $minify_css_value );
		add_filter( 'pre_get_rocket_option_minify_js', $minify_js_value );

		apply_filters( 'update_option_qode_options_proya', $old_value, $value );

		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );

		remove_filter( 'pre_get_rocket_option_minify_css', $minify_css_value );
		remove_filter( 'pre_get_rocket_option_minify_js', $minify_js_value );
	}
}
