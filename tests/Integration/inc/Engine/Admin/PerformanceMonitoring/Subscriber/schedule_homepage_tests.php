<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::schedule_homepage_tests
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_ScheduleHomepageTest extends TestCase {

	use IsolateHookTrait;

	protected $config;

	protected $container;

	public function set_up() {
		parent::set_up();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_first_install', 'schedule_homepage_tests' );
		// @phpstan-ignore-next-line
		$this->container = apply_filters('rocket_container', null);
	}

	public function tear_down() {
		$this->tearDownSettings();
		$this->restoreWpHook( 'wp_rocket_first_install' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->config = $config;
		$this->setUpSettings();

		// Get count before triggering action
		$actions_before = $this->getScheduledActionsCount();

		do_action( 'wp_rocket_first_install' );

		// Get count after triggering action
		$actions_after = $this->getScheduledActionsCount();
		$new_actions = $actions_after - $actions_before;

		$this->assertSame( $expected['schedule_actions'], $new_actions );

	}

	public function getScheduledActionsCount() : int {
		// Get container services
		$queue = $this->container->get( 'pm_queue' );

		return count( $queue->get_pending_actions() );
	}
}
