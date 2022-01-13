<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Bridge;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Bridge::exclude_static_file_generation
 * @group  BridgeTheme
 * @group  ThirdParty
 */
class excludeStaticFileGeneration extends TestCase
{
	private static $container;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$container = apply_filters('rocket_container', '');
	}

	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$container->get('event_manager')->remove_subscriber(self::$container->get('bridge_subscriber'));
	}

	public function setUp(): void
	{
		parent::setUp();

		add_filter('pre_option_stylesheet', [$this, 'set_stylesheet']);
		add_filter('pre_option_stylesheet_root', [$this, 'set_stylesheet_root']);

		self::$container->get('event_manager')->add_subscriber(self::$container->get('bridge_subscriber'));
		$this->unregisterAllCallbacksExcept('rocket_exclude_static_dynamic_resources', 'exclude_static_file_generation');
	}

	public function tearDown()
	{
		global $wp_theme_directories;
		unset($wp_theme_directories['virtual']);

		$this->restoreWpFilter('rocket_exclude_static_dynamic_resources');
		remove_filter('pre_option_stylesheet', [$this, 'set_stylesheet']);
		remove_filter('pre_option_stylesheet_root', [$this, 'set_stylesheet_root']);

		parent::tearDown();
	}

	public function set_stylesheet()
	{
		return 'Bridge';
	}

	public function set_stylesheet_root()
	{
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = 'http://example.org/wp-content/themes';

		return 'http://example.org/wp-content/themes';
	}

	public function testShouldReturnExpected()
	{
		$this->assertSame(
			[
				'wp-content/themes/bridge/js/default_dynamic_callback.php',
				'wp-content/themes/bridge/css/style_dynamic_callback.php',
			],
			apply_filters('rocket_exclude_static_dynamic_resources', [])
		);
	}
}
