<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_LockUrl extends AdminTestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config,$expected) {
		foreach ($config['data'] as $cache) {
			self::addCache($cache);
		}

		do_action('rocket_preload_lock_url', $config['url']);

		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}
	}
}
