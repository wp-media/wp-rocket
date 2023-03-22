<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressable;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressable;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressable::fix_wp_includes_path
 *
 */
class Test_fixWpIncludesPath extends TestCase {

	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressable();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, $this->subscriber->fix_wp_includes_path($config));
    }
}
