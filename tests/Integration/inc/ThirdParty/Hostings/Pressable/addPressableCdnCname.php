<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressable;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressable::add_pressable_cdn_cname
 * @group Pressable
 */
class Test_addPressableCdnCname extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, apply_filters('rocket_cdn_cnames', $config));
    }
}
