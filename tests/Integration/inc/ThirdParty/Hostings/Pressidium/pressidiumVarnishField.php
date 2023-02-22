<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::pressidium_varnish_field
 * @group Pressidium
 */
class Test_pressidiumVarnishField extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, apply_filters('rocket_varnish_field_settings', $config));
    }
}
