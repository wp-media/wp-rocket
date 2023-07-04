<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::clear_generated_css
 */
class Test_clearGeneratedCss extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
        do_action('after_rocket_clean_domain');
    }
}
