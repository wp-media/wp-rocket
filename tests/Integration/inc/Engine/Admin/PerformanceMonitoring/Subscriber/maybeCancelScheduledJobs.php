<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::maybe_cancel_scheduled_jobs
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class TestMaybeCancelScheduledJobs extends TestCase {
	protected $pre_get_rocket_option_performance_monitoring;

	protected $pre_schedule_event;

	protected $unschedule_event;

    public function set_up() {
        parent::set_up();

        $this->unregisterAllCallbacksExcept('init', 'maybe_cancel_scheduled_jobs', 10);
        $this->unregisterAllCallbacksExcept('rocket_options_changed', 'maybe_cancel_scheduled_jobs', 10);

        $this->unschedule_event = false;
		add_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'mock_pre_get_rocket_option_performance_monitoring' ] );
    	add_filter( 'pre_get_scheduled_event', [ $this, 'mock_pre_schedule_event' ] );
    	add_filter( 'pre_unschedule_event', [ $this, 'mock_pre_unschedule_event' ] );
	}

    public function tear_down() {
		remove_filter( 'pre_unschedule_event', [ $this, 'mock_pre_unschedule_event' ] );
		remove_filter( 'pre_get_scheduled_event', [ $this, 'mock_pre_schedule_event' ] );
		remove_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'mock_pre_get_rocket_option_performance_monitoring' ] );
		$this->restoreWpHook('init');
        $this->restoreWpHook('rocket_options_changed');

        parent::tear_down();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected) {
		$this->pre_get_rocket_option_performance_monitoring = $config['performance_monitoring_enabled'];
		$this->pre_schedule_event = $config['event_scheduled'];
		$this->unschedule_event = false;

		$container = apply_filters('rocket_container', null);
		$user = $container->get('user');
		$user->set_user($config['user_data']);

        // Trigger the method via the hook
        do_action('init');

        // Assert the event was unscheduled or not as expected
        $this->assertEquals($expected['unscheduled'], $this->unschedule_event);
    }

	public function mock_pre_get_rocket_option_performance_monitoring() {
		return $this->pre_get_rocket_option_performance_monitoring;
	}

	public function mock_pre_unschedule_event() {
		$this->unschedule_event = true;
	}

	public function mock_pre_schedule_event() {
		return $this->pre_schedule_event ? (object) [
			'hook' => 'retest_all_pages',
			'timestamp' => time() + DAY_IN_SECONDS
		] : false;
	}
}
