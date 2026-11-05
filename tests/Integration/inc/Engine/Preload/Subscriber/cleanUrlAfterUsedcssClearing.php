<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::clean_url_after_usedcss_cleared
 *
 * @group Preload
 */
class Test_CleanUrlAfterUsedcssClearing extends AdminTestCase {
	protected $manual_preload;

	public function set_up() {
		parent::set_up();

		self::installPreloadCacheTable();

		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
	}

	public function tear_down() {
		self::uninstallPreloadCacheTable();

		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->manual_preload = $config['manual_preload'];
		foreach ($config['data'] as $cache) {
			self::addCache($cache);
		}

		do_action($config['hook'], $config['arg']);

		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}

		foreach ($expected['missing'] as $cache) {
			$this->assertFalse(self::cacheFound($cache));
		}
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
