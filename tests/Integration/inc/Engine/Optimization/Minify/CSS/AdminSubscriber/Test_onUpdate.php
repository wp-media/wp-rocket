<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use function Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::on_update
 */
class Test_onUpdate extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		if($expected) {
			Functions\expect('rocket_clean_minify');
			Functions\expect('rocket_clean_domain');
		}else {
			Functions\expect('rocket_clean_minify')->never();
			Functions\expect('rocket_clean_domain')->never();
		}
		do_action('wp_rocket_upgrade', '3.15', $config['old_version']);
    }
}
