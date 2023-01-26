<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\WpDiscuz;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\WpDiscuz;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\WpDiscuz::force_mobile_cache
 * @group   WpDiscuz
 */
class Test_ForceMobileCache extends TestCase {
	protected $subscriber;

	protected function set_up() {
		parent::set_up();
		$this->subscriber = new WpDiscuz();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertSame($expected, $this->subscriber->force_mobile_cache($config));
	}
}
