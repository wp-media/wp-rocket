<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use NinukisCaching;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::clean_post
 *
 */
class Test_cleanPost extends TestCase {

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
		Functions\when('wp_parse_url')->justReturn($config['parsed_url']);

		$ninukis_caching = Mockery::mock('overload:' . NinukisCaching::class);
		$ninukis_caching->expects()->get_instance()->andReturn($ninukis_caching);
		$ninukis_caching->expects()->get_paths($config['url'])->andReturn($config['path']);
		$ninukis_caching->expects()->purge_cache($config['path']);

		$this->subscriber->purge_url($config['url']);
	}
}
