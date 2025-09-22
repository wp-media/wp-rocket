<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\SettingsSubscriber;

/**
 * @covers \WP_Rocket\Engine\Admin\PerformanceMonitoring\SettingsSubscriber::display_addon_status
 *
 * @group License
 * @group AdminOnly
 */
class Test_DisplayAddonStatus extends TestCase {

	protected $display_addon_status;

	protected $customer_data;

	protected $is_live_site;

    public function set_up() {
        parent::set_up();

		add_filter('rocket_display_addon_status', [$this, 'filter_rocket_display_addon_status']);
		add_filter('rocket_is_live_site', [$this, 'filter_rocket_is_live_site']);
	}

    public function tear_down() {
        remove_filter('rocket_display_addon_status', [$this, 'filter_rocket_display_addon_status']);
        remove_filter('rocket_is_live_site', [$this, 'filter_rocket_is_live_site']);

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected) {
		$this->display_addon_status = $config['rocket_display_addon_status'];
		$this->is_live_site = $config['is_live_site'] ?? true;

		$container = apply_filters('rocket_container', null);

		$container->get('user')->set_user($config['customer_data']);

        if (isset($config['date_format'])) {
            update_option('date_format', $config['date_format']);
        }

        ob_start();
        do_action('rocket_dashboard_after_account_data');
        $actual = ob_get_clean();

        $this->assertStringContainsString($this->format_the_html($expected), $this->format_the_html($actual));
    }

    public function filter_rocket_display_addon_status() {
        return $this->display_addon_status;
    }

    public function filter_rocket_is_live_site() {
        return $this->is_live_site;
    }

    protected function format_the_html($html) {
        return preg_replace('/[\s]+/', ' ', trim(preg_replace('/[\r\n\t]+/', '', $html)));
    }
}
