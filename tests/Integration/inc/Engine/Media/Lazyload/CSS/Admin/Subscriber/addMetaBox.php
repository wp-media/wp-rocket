<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::add_meta_box
 */
class Test_addMetaBox extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, apply_filters('rocket_meta_boxes_fields', $config['fields']));
    }
}
