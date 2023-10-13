<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::clear_cache_after_pressidium
 * @group Pressidium
 */
class Test_clearCacheAfterPressidium extends AdminTestCase {
	use CapTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();
	}

	public function set_up()
	{
		parent::set_up();
		$_POST['purge-all'] = true;
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		CapTrait::resetAdminCap();
	}

	public function tear_down()
	{
		unset($_POST['purge-all']);
		parent::tear_down();
	}

	public function testShouldReturnExpected()
	{
		$current_user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $current_user );

		Functions\expect( 'check_admin_referer' )
			->once()
			->andReturn( true );

		Functions\expect('rocket_clean_domain');
		Functions\expect('run_rocket_sitemap_preload');

		do_action('admin_init');
	}
}
