<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use Ninukis_Plugin;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::clean_pressidium
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
		$ninukis_plugin = Mockery::mock('overload:' . Ninukis_Plugin::class);
		$ninukis_plugin->expects()->get_instance()->andReturn($ninukis_plugin);
		$ninukis_plugin->expects()->purgeAllCaches();
		$this->subscriber->clean_pressidium();
	}
}
