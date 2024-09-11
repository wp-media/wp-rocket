<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::lock_url
 *
 * @group Preload
 */
class Test_LockUrl extends AdminTestCase {
	public function set_up() {
		parent::set_up();

		self::installPreloadCacheTable();
	}

	public function tear_down() {
		self::uninstallPreloadCacheTable();

		parent::tear_down();
	}

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
