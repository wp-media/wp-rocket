<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Uncode;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Uncode::exclude_delay_js
 *
 * @group  Uncode
 * @group  ThirdParty
 */
class Test_ExcludeDelayJs extends FilesystemTestCase {
	private static $container;
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Uncode/excludeDelayJs.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'uncode' ) );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		add_filter( 'template_directory_uri', [ $this, 'set_template_uri' ] );

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'uncode' ) );
	}

	public function tear_down() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'template_directory_uri', [ $this, 'set_template_uri' ] );
		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		parent::tear_down();
	}

	public function set_template_uri() {
		return 'http://example.org/wp-content/themes/uncode';
	}

	public function set_stylesheet() {
		return 'uncode';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_delay_js_exclusions', $config['exclusions'] )
		);
	}
}
