<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::update_cache_row
 * @group  Preload
 */
class Test_UpdateCacheRow extends AdminTestCase
{
	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		foreach ($config['links'] as $link) {
			self::addCache($link);
		}



		do_action('rocket_after_process_buffer');

		if(! $config['is_preloaded']) {
			$this->assertGreaterThan( 0, did_action('rocket_preload_completed') );
		}

		foreach ($expected['links'] as $link) {
			$this->assertSame(true, self::cacheFound($link));
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'updateCacheRow' );
	}
}
