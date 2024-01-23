<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::update_cache_row
 * @group  Preload
 */
class Test_UpdateCacheRow extends AdminTestCase
{
	protected $config;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public function set_up()
	{
		parent::set_up();
		add_filter('rocket_preload_exclude_urls', [$this, 'excluded']);
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	public function tear_down()
	{
		remove_filter('rocket_preload_exclude_urls', [$this, 'excluded']);
		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'rucss']);
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->config = $config;

		foreach ($config['links'] as $link) {
			self::addCache($link);
		}

		do_action('rocket_after_process_buffer');


		if($config['is_preloaded']) {
			$this->assertGreaterThan( 0, did_action('rocket_preload_completed') );
		}

		foreach ($expected['links'] as $link) {
			$this->assertSame($expected['exists'], self::cacheFound($link));
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'updateCacheRow' );
	}

	public function excluded($regexes): array {
		return array_merge($regexes, $this->config['regexes']);
	}

	public function manual_preload() {
		return $this->config['manual_preload'];
	}
}
