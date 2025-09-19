<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::maybe_schedule_next_test
 *
 * @group PerformanceMonitoring
 */
class Test_MaybeScheduleNextTest extends TestCase {
    private $event_scheduled = false;
    private $scheduled_time = 1727000000; // Future timestamp for testing

	protected $pre_get_rocket_option_performance_monitoring;

	protected $schedule_event;

    public function set_up() {
        parent::set_up();

        // Unregister all callbacks except the one we're testing
        $this->unregisterAllCallbacksExcept('init', 'maybe_schedule_next_test', 10);

        // Reset flag between tests
        $this->event_scheduled = false;
		add_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'mock_pre_get_rocket_option_performance_monitoring' ] );
    	add_filter( 'pre_get_scheduled_event', [ $this, 'mock_pre_get_scheduled_event' ] );
    	add_filter( 'pre_schedule_event', [ $this, 'mock_pre_schedule_event' ] );
	}

    public function tear_down() {
		remove_filter( 'pre_schedule_event', [ $this, 'mock_pre_schedule_event' ] );
		remove_filter( 'pre_get_scheduled_event', [ $this, 'mock_pre_get_scheduled_event' ] );
		remove_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'mock_pre_get_rocket_option_performance_monitoring' ] );
        $this->restoreWpHook('init');

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
		$this->pre_get_rocket_option_performance_monitoring = $config['performance_monitoring_enabled'];
		$this->schedule_event = false;
		$this->event_scheduled = $config['event_scheduled'];
		$container = apply_filters('rocket_container', null);
		$user = $container->get('user');
		$user->set_user($config['user_data']);

        // Mock wp_next_scheduled and wp_schedule_event
        $this->mockScheduleFunctions($config['event_scheduled']);

        // Trigger the method via the hook
        do_action('init');

        // Assert the event was scheduled or not as expected
        $this->assertEquals($expected['scheduled'], $this->schedule_event);
    }

    /**
     * Mock the WordPress scheduling functions
     *
     * @param bool $event_scheduled Whether the event is already scheduled
     */
    private function mockScheduleFunctions($event_scheduled) {
        // Mock wp_next_scheduled to return true or false depending on test case
        add_filter('pre_option_cron', function() use ($event_scheduled) {
            $crons = [];

            if ($event_scheduled) {
                $crons[$this->scheduled_time]['retest_all_pages'][md5('retest_all_pages')] = [
                    'schedule' => 'daily',
                    'args' => [],
                    'interval' => 86400,
                ];
            }

            return $crons;
        });

        // Mock wp_schedule_event to set our flag
        add_filter('pre_update_option_cron', function($new_cron, $old_cron) {
            // Check if our event was added
            if (isset($new_cron) && is_array($new_cron)) {
                foreach ($new_cron as $timestamp => $hooks) {
                    if (isset($hooks['retest_all_pages'])) {
                        $this->event_scheduled = true;
                        break;
                    }
                }
            }

            // Return the old value to prevent actual scheduling
            return $old_cron;
        }, 10, 2);
    }

	public function mock_pre_get_rocket_option_performance_monitoring() {
		return $this->pre_get_rocket_option_performance_monitoring;
	}

	public function mock_pre_schedule_event() {
		$this->schedule_event = true;
	}

	public function mock_pre_get_scheduled_event() {
		return $this->event_scheduled ? (object) [
			'hook' => 'retest_all_pages',
			'timestamp' => time() + DAY_IN_SECONDS
		] : false;
	}
}
