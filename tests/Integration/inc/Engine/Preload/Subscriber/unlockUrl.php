<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_UnlockUrl extends AdminTestCase
{
	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config,$expected) {
		foreach ($config['data'] as $cache) {
			self::addCache($cache);
		}

		do_action('rocket_preload_unlock_url', $config['url']);

		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}
	}
}
