<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\Sucuri\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\Sucuri\Subscriber::add_cdn_helper_message
 */
class Test_addCdnHelperMessage extends TestCase {

	public function set_up()
	{
		add_filter( 'pre_get_rocket_option_sucury_waf_cache_sync', [ $this, 'sucury_waf_cache_sync'] );
		parent::set_up();
	}

	public function tear_down()
	{
		remove_filter( 'pre_get_rocket_option_sucury_waf_cache_sync', [ $this, 'sucury_waf_cache_sync'] );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected )
	{
		$this->config = $config;

		$this->assertSame($expected, apply_filters('rocket_cdn_helper_addons', $config['addons']));
	}

	public function sucury_waf_cache_sync() {
		return $this->config['is_enabled'];
	}
}
