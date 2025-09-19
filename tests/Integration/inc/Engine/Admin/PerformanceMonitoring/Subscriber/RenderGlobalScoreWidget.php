<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::render_global_score_widget
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_RenderGlobalScoreWidget extends TestCase {
    private $performance_monitoring;
    private const TRANSIENT_NAME = 'wpr_global_score_data';

    public function set_up() {
        parent::set_up();

        $this->unregisterAllCallbacksExcept('rocket_dashboard_sidebar', 'render_global_score_widget', 10);
    }

    public function tear_down() {
        $this->restoreWpHook('rocket_dashboard_sidebar');
        remove_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'set_performance_monitoring' ] );
        
        delete_transient( self::TRANSIENT_NAME );

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
        $this->performance_monitoring = $config['performance_monitoring'];
		add_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'set_performance_monitoring' ] );

        set_transient( self::TRANSIENT_NAME, $config['global_score_data'], MINUTE_IN_SECONDS );

        $actual = $this->get_actual_html();

        $this->assertStringContainsString($expected, $actual);
    }

    private function get_actual_html() {
        ob_start();
        do_action('rocket_dashboard_sidebar');
        $html = ob_get_clean();

        return $this->format_the_html($html);
    }

    public function set_performance_monitoring() {
        return $this->performance_monitoring;
    }
}
