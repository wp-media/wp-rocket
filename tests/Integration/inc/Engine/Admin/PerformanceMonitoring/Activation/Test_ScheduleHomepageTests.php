<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Activation;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Activation\Activation::schedule_homepage_tests
 *
 * @group PerformanceMonitoring
 */
class Test_ScheduleHomepageTests extends TestCase {
	use IsolateHookTrait;

	public function set_up() {
		parent::set_up();

		$this->setUpSettings();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_first_install', 'schedule_homepage_tests', 20 );
	}

	public function tear_down() {
		$this->tearDownSettings();
		$this->restoreWpHook( 'wp_rocket_first_install' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldScheduleHomepageTestsAsExpected( $config, $expected ) {
		// Set up test conditions for first install check
		if ( isset( $config['wp_rocket_version'] ) ) {
			\update_option( 'wp_rocket_settings', [ 'wp_rocket_version' => $config['wp_rocket_version'] ] );
		} else {
			\delete_option( 'wp_rocket_settings' );
		}

		// Get container services
		$container = apply_filters( 'rocket_container', null );
		$activation = $container->get( 'pm_activation' );
		$queue = $container->get( 'pm_queue' );

		// Get initial action count
		$initial_actions = $queue->get_pending_actions();
		$initial_count = count( $initial_actions );

		// Register the hook (this tests the activate() method)
		$activation->activate();

		// Trigger first install (this tests the whole feature)
		do_action( 'wp_rocket_first_install' );

		// Get final action count
		$final_actions = $queue->get_pending_actions();
		$final_count = count( $final_actions );

		// Verify result
		if ( $expected['should_schedule_tests'] ) {
			$this->assertEquals( $initial_count + 2, $final_count, 'Should schedule 2 homepage tests (desktop + mobile)' );
		} else {
			$this->assertEquals( $initial_count, $final_count, 'Should not schedule tests when not first install' );
		}
	}

	public function configTestData() {
		return [
			'testShouldScheduleWhenFirstInstall' => [
				'config' => [
					// No wp_rocket_version = first install
				],
				'expected' => [
					'should_schedule_tests' => true,
				],
			],
			'testShouldNotScheduleWhenUpgrade' => [
				'config' => [
					'wp_rocket_version' => '3.16.0', // Existing version = upgrade
				],
				'expected' => [
					'should_schedule_tests' => false,
				],
			],
		];
	}
}
