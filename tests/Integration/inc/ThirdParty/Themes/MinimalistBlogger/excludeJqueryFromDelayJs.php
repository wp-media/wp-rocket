<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\MinimalistBlogger;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Themes\MinimalistBlogger;

/**
 * @covers \WP_Rocket\ThirdParty\MinimalistBlogger::exclude_jquery_from_delay_js
 *
 * @group  ThirdParty
 */
class Test_excludeJqueryFromDelayJs extends TestCase
{
	private static $container;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();

		self::$container = apply_filters('rocket_container', '');
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();

		self::$container->get('event_manager')->remove_subscriber(self::$container->get('minimalist_blogger'));
	}

	public function set_up()
	{
		parent::set_up();

		add_filter('pre_option_stylesheet', [$this, 'set_stylesheet']);

		self::$container->get('event_manager')->add_subscriber(self::$container->get('minimalist_blogger'));
	}

	public function tear_down()
	{
		global $wp_theme_directories;
		unset($wp_theme_directories['virtual']);

		remove_filter('pre_option_stylesheet', [$this, 'set_stylesheet']);
		parent::tear_down();
	}

	public function set_stylesheet()
	{
		return 'minimalist-blogger';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected($config, $expected)
	{
		$minimalist_blogger = new MinimalistBlogger();
		$this->assertSame(
			$expected,
			$minimalist_blogger->exclude_jquery_from_delay_js($config['excluded'])
		);
	}
}
