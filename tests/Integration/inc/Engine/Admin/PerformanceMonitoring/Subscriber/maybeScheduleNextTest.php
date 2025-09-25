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

	protected $pre_get_rocket_option_performance_monitoring;

	protected $performance_monitoring_schedule_frequency;

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
    	add_filter( 'pre_get_rocket_option_performance_monitoring_schedule_frequency', [ $this, 'mock_performance_monitoring_schedule_frequency' ] );
	}

    public function tear_down() {
		remove_filter( 'pre_get_rocket_option_performance_monitoring_schedule_frequency', [ $this, 'mock_performance_monitoring_schedule_frequency' ] );
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
		$this->performance_monitoring_schedule_frequency = $config['schedule_frequency'];
		$this->schedule_event = false;
		$this->event_scheduled = $config['event_scheduled'];
		$container = apply_filters('rocket_container', null);
		$user = $container->get('user');
		$user->set_user($config['user_data']);

        // Trigger the method via the hook
        do_action('init');

        // Assert the event was scheduled or not as expected
        $this->assertEquals($expected['scheduled'], $this->schedule_event);
    }

	public function mock_pre_get_rocket_option_performance_monitoring() {
		return $this->pre_get_rocket_option_performance_monitoring;
	}

	public function mock_pre_schedule_event() {
		$this->schedule_event = true;
	}

	public function mock_pre_get_scheduled_event() {
		return $this->event_scheduled ? (object) [
			'hook' => 'wpr_pma_retest_all_pages',
			'timestamp' => time() + DAY_IN_SECONDS
		] : false;
	}

	public function mock_performance_monitoring_schedule_frequency() {
		return $this->performance_monitoring_schedule_frequency;
	}
}
