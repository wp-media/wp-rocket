<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Shoptimizer;

use WP_Rocket\Tests\Integration\TestCase;

use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Shoptimizer::exclude_jquery_deferjs_with_cart_drawer
 */
class Test_excludeJqueryDeferjsWithCartDrawer extends TestCase {

	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'shoptimizer' ) );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'shoptimizer' ) );
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {

		Functions\when('shoptimizer_get_option')->justReturn($config['option']);

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_defer_js', $config['exclusions'] )
		);
    }

	public function set_stylesheet() {
		return 'Shoptimizer';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = 'http://example.org/wp-content/themes';

		return 'http://example.org/wp-content/themes';
	}

	public function tear_down() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		parent::tear_down();
	}
}
