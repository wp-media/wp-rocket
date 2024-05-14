<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::clean_partial_cache
 */
class Test_CleanPartialCache extends AdminTestCase
{
	use IsolateHookTrait;

	protected $manual_preload;

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		$this->unregisterAllCallbacksExcept('after_rocket_clean_post', 'clean_partial_cache');
	}

	public function tear_down()
	{
		$this->restoreWpHook('after_rocket_clean_post');
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
		do_action($config['hook'], $config['object'], $config['urls'], $config['lang']);
		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
