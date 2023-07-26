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

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	public function set_up()
	{
		parent::set_up();
    	add_filter('rocket_preload_exclude_urls', [$this, 'excluded']);
		add_filter('rocket_preload_query_string', [$this, 'query_enabled']);
		add_filter('rocket_cache_ignored_parameters', [$this, 'excluded_query_params']);
	}

	public function tear_down()
	{
		remove_filter('rocket_preload_query_string', [$this, 'query_enabled']);
    	remove_filter('rocket_preload_exclude_urls', [$this, 'excluded']);
    	remove_filter('rocket_cache_ignored_parameters', [$this, 'excluded_query_params']);
		unset($GLOBALS['_GET']);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->config = $config;

		foreach ($config['links'] as $link) {
			self::addCache($link);
		}

		$_GET = $config['params'];

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

	public function query_enabled() {
		return $this->config['query_enabled'];
  }

	public function excluded($regexes): array {
		return array_merge($regexes, $this->config['regexes']);
	}

	public function excluded_query_params($exclusions) {
		return  array_merge($exclusions, $this->config['excluded_params']);
	}
}
