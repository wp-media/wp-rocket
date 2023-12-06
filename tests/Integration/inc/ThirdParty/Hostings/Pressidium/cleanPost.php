<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use Mockery;
use NinukisCaching;


/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::clean_post
 * @group Pressidium
 */
class Test_cleanPost extends TestCase
{
	/**
	 * @var NinukisCaching
	 */
	protected $ninukis_caching;

	public function set_up(): void
	{
		parent::set_up();
		$this->ninukis_caching = Mockery::mock('overload:' . NinukisCaching::class);
	}

	public function tear_down(): void
	{
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected($config, $expected): void
	{
		$post = $this->factory->post->create_and_get( $config['post'] );

		$this->ninukis_caching->expects()->purge_url($config['url']);

		do_action('after_rocket_clean_post', $post, $config['url'], '');
	}
}
