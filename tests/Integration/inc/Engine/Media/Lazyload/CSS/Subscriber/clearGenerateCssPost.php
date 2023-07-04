<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::clear_generate_css_post
 */
class Test_clearGenerateCssPost extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
        do_action('after_rocket_clean_post', $config['post']);
    }
}
