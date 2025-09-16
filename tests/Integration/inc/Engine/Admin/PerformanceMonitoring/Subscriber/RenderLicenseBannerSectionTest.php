<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::render_license_banner_section
 *
 * @group PerformanceMonitoring
 */
class RenderLicenseBannerSectionTest extends TestCase {

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
