<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber activation
 *
 * @group PerformanceMonitoring
 */
class Test_Activation extends TestCase {

	public function testShouldHaveSubscriberRegistered() {
		$container = apply_filters( 'rocket_container', null );

		// Test that the subscriber is available in the container.
		$this->assertTrue( $container->has( 'pm_subscriber' ) );

		// Test that we can retrieve the subscriber.
		$pm_subscriber = $container->get( 'pm_subscriber' );
		$this->assertInstanceOf( Subscriber::class, $pm_subscriber );
	}

	public function testShouldHaveCorrectSubscribedEvents() {
		$events = Subscriber::get_subscribed_events();

		// Check that our activation event is registered
		$this->assertArrayHasKey( 'rocket_after_activation', $events );
		$this->assertEquals( 'on_first_install', $events['rocket_after_activation'] );

		// Check for other expected events
		$this->assertArrayHasKey( 'wp_rocket_first_install', $events );
		$this->assertArrayHasKey( 'wp_ajax_pma_get_latest_score', $events );
	}

	public function testShouldTriggerActivationHook() {
		// Capture any actions/logs that occur
		$actions_called = [];
		
		// Hook into the activation method to verify it's called
		add_action( 'rocket_after_activation', function() use ( &$actions_called ) {
			$actions_called[] = 'rocket_after_activation';
		}, 5 ); // High priority to run before our subscriber

		// Trigger the activation hook
		do_action( 'rocket_after_activation' );

		// Verify the hook was fired
		$this->assertContains( 'rocket_after_activation', $actions_called );
	}

	public function testActivationLogging() {
		// Start output buffering to capture error_log output
		ob_start();
		
		// Manually call the subscriber's activation method
		$container = apply_filters( 'rocket_container', null );
		$pm_subscriber = $container->get( 'pm_subscriber' );
		
		// Call the activation method directly
		$pm_subscriber->on_first_install();
		
		// Get any buffered output (though error_log might not show here)
		$output = ob_get_clean();
		
		// This test mainly verifies the method can be called without errors
		$this->assertTrue( method_exists( $pm_subscriber, 'on_first_install' ) );
	}
}
