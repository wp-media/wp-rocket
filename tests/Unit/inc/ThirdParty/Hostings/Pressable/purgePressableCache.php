<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressable;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressable;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressable::purge_pressable_cache
 *
 */
class Test_purgePressableCache extends TestCase {

	/**
	 * @var Pressable
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressable();
	}

	public function testShouldReturnExpected( )
	{
		Functions\expect('wp_cache_flush');
		$this->subscriber->purge_pressable_cache();
	}
}
