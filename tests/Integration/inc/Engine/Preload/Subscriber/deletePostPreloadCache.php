<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\FilterTrait;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::delete_url_on_not_found
 * @group  Preload
 */
class Test_DeletePostPreloadCache extends AdminTestCase
{
	use FilterTrait;

	protected $manual_preload;

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

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		$this->unregisterAllCallbacksExcept('pre_handle_404', 'delete_url_on_not_found');
	}

	public function tear_down()
	{
		$this->restoreWpFilter('pre_handle_404');
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

		do_action('pre_handle_404', $config['url']);

		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
