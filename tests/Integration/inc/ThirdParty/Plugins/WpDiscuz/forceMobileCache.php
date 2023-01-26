<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\WpDiscuz;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\WpDiscuz::force_mobile_cache
 * @group   WpDiscuz
 */
class Test_ForceMobileCache extends TestCase {
	use FilterTrait;
	public function set_up() {
		parent::set_up();
		$this->unregisterAllCallbacksExcept('option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), 'force_mobile_cache');
	}

	public function tear_down() {
		$this->restoreWpFilter('option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ));
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertSame($expected, apply_filters('option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), $config));
	}
}
