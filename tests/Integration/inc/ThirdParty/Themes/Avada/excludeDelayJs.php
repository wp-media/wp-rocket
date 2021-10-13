<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::exclude_delay_js
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_ExcludeDelayJs extends TestCase {
	private static $container;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'avada_subscriber' ) );
	}

	public function setUp(): void {
		parent::setUp();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'avada_subscriber' ) );
	}

	public function tearDown() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		parent::tearDown();
	}

	public function set_stylesheet() {
		return 'Avada';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = 'http://example.org/wp-content/themes';

		return 'http://example.org/wp-content/themes';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $exclusions, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_delay_js_exclusions', $exclusions )
		);
	}
}
