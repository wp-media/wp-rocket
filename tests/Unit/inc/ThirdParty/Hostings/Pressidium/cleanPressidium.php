<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use NinukisCaching;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::clean_pressidium
 *
 */
class Test_cleanPressidium extends TestCase {

	/**
	 * @var Pressidium
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressidium();
	}

	public function testShouldReturnExpected()
	{
		$ninukis_plugin = Mockery::mock('overload:' . NinukisCaching::class);
		$ninukis_plugin->expects()->get_instance()->andReturn($ninukis_plugin);
		$ninukis_plugin->expects()->purgeAllCaches();
		$this->subscriber->clean_pressidium();
	}
}
