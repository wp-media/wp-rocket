<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::insert_lazyload_script
 */
class Test_insertLazyloadScript extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
        do_action('wp_enqueue_scripts');
    }
}
