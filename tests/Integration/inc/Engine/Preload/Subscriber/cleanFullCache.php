<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::clean_full_cache
 *
 * @group Preload
 */
class Test_CleanFullCache extends AdminTestCase {
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
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->manual_preload = $config['manual_preload'];
		foreach ($config['data'] as $cache) {
			self::addCache($cache);
		}
		do_action('rocket_after_clean_domain', 'whatever',[]);

		foreach ($expected['data'] as $cache) {
			$this->assertSame($expected['exists'], self::cacheFound($cache));
		}
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
