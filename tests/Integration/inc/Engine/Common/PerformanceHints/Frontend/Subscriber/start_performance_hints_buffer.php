<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\PerformanceHints\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Buffer\Tests;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Frontend\Subscriber::start_performance_hints_buffer
 *
 * @group PerformanceHints
 */
class Test_start_performance_hints_buffer extends TestCase {

    public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists().
		self::installAtfTable();
		self::installLrcTable();
	}

	public static function tear_down_after_class() {
		self::uninstallAtfTable();
		self::uninstallLrcTable();

		parent::tear_down_after_class();
	}

    public function set_up() {
        parent::set_up();

        $this->unregisterAllCallbacksExcept( 'template_redirect', 'start_performance_hints_buffer', 3 );
    }

    public function tear_down() {
        $this->restoreWpHook( 'template_redirect' );

        // Reset GET parameters
        $_GET = [];

        parent::tear_down();
    }

	/**
	 * @dataProvider configTestData
	 */
    public function testShouldReturnAsExpected($config, $expected) {
        // Set up GET parameters
        $_GET = $config;
        
        ob_start();

        $before_ob_level = ob_get_level();

        do_action('template_redirect');

        $after_ob_level = ob_get_level();

        $this->loadBuffer();

        $this->assertEquals($expected, $after_ob_level - $before_ob_level);
    }

    protected function loadBuffer() {
        echo '<html><head><title></title></head><body></body></html>';
        ob_get_clean();
    }
}