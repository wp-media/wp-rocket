<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::render_license_banner_section
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class TestRenderLicenseBannerSection extends TestCase {
	use CapTrait;

	private static $user_id;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public static function tearDown_after_class() {
		parent::tear_down_after_class();
		self::resetAdminCap();
	}

    public function set_up() {
        parent::set_up();

        $this->unregisterAllCallbacksExcept('rocket_insights_tab_content', 'render_license_banner_section', 10);
    }

    public function tear_down() {
        $this->restoreWpHook('rocket_insights_tab_content');

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
		wp_set_current_user( self::$user_id );

        // Set up user data for the test
        $container = apply_filters('rocket_container', null);
        $container->get('user')->set_user($config['customer_data']);

        $actual = $this->getActualHtml();

        if (empty($expected)) {
            $this->assertEmpty($actual);
        } else {
            $this->assertStringContainsString($this->format_the_html($expected), $actual);
        }
    }

    private function getActualHtml() {
        ob_start();
        do_action('rocket_insights_tab_content');
        $html = ob_get_clean();

        return $this->format_the_html($html);
    }
}
