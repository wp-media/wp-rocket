<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Bridge;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\ThirdParty\Themes\Bridge;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Bridge::maybe_clear_cache
 *
 * @group Themes
 */
class Test_MaybeClearCache extends FilesystemTestCase {
	use DBTrait;

	private $container;
	private $event;
	private $subscriber;
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Bridge/maybeClearCache.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'minify_css_value' ] );
		add_filter( 'pre_get_rocket_option_minify_js', [ $this, 'minify_js_value' ] );
	}

	public function tear_down() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		delete_option( 'qode_options_proya' );

		$this->event->remove_subscriber( $this->subscriber );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'minify_css_value' ] );
		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'minify_js_value' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCacheWhenSettingsMatch( $old_value, $value, $settings, $expected ) {
		$this->settings = $settings;
		$this->subscriber = new Bridge( $this->container->get( 'options' ) );

		$this->event->add_subscriber( $this->subscriber );

		apply_filters( 'update_option_qode_options_proya', $old_value, $value );

		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertSame( $expected, $this->filesystem->exists( 'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}

	public function set_stylesheet() {
		return 'bridge';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}

	public function minify_css_value() {
		return $this->settings['minify_css'];
	}

	public function minify_js_value() {
		return $this->settings['minify_js'];
	}
}
