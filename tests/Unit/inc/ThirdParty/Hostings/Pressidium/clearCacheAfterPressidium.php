<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;
use Brain\Monkey\Functions;
/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::clear_cache_after_pressidium
 *
 */
class Test_clearCacheAfterPressidium extends TestCase {

	/**
	 * @var Pressidium
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressidium();
		$_POST['purge-all'] = true;
		if(! defined('WP_NINUKIS_WP_NAME')) {
			define('WP_NINUKIS_WP_NAME', 'WP_NINUKIS_WP_NAME');
		}
	}

	public function testShouldReturnExpected()
	{
		Functions\expect('current_user_can')->with('manage_options')->andReturn(true);
		Functions\expect('check_admin_referer')->with(WP_NINUKIS_WP_NAME . '-caching' )->andReturn(true);
		Functions\expect('rocket_clean_domain');
		$this->subscriber->clear_cache_after_pressidium();
	}
}
