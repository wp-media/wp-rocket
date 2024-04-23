<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;
use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;


/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::on_update
 */
class Test_onUpdate extends TestCase {

    /**
     * @var AdminSubscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->subscriber = new AdminSubscriber();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		if($expected) {
			Functions\expect('rocket_clean_domain');
		}else {
			Functions\expect('rocket_clean_domain')->never();
		}
        $this->subscriber->on_update('3.15', $config['old_version']);
    }
}
