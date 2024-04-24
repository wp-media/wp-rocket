<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use NinukisCaching;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::purge_url
 *
 */
class Test_purgeUrl extends TestCase {

	/**
	 * @var Pressidium
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressidium();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected )
	{
		Functions\when( 'wp_parse_url' )->alias( function( $url = '' ) use ($config) {
			return $config['parsed_url'][$url];
		} );

		$ninukis_plugin = Mockery::mock('overload:' . NinukisCaching::class);
		$ninukis_plugin->expects()->get_instance()->andReturn($ninukis_plugin);

		$ninukis_plugin->expects()->get_paths($config['urls'])->andReturn($config['path']);
		$ninukis_plugin->expects()->purge_cache($config['path']);
		$this->subscriber->purge_url($config['urls']);
	}
}
