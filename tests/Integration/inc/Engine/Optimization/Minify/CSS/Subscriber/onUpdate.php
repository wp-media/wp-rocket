<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber::on_update
 */
class Test_onUpdate extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		if($expected) {
			Functions\expect('rocket_clean_minify');
		}else {
			Functions\expect('rocket_clean_minify')->never();
		}
		do_action('wp_rocket_upgrade', $config['old_version'], '3.15');
    }
}
