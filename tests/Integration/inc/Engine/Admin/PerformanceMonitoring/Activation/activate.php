<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Activation;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Activation\Activation::activate
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_Activate extends TestCase {

	use IsolateHookTrait;

	public function set_up() {
		parent::set_up();

		$this->setUpSettings();
		$this->unregisterAllCallbacksExcept( 'rocket_activation', 'schedule_homepage_tests' );
	}

	public function tear_down() {
		$this->tearDownSettings();
		$this->restoreWpHook( 'rocket_activation' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		if ( $config['is_first_install'] ) {
			delete_option('wp_rocket_settings');
		} else {
			\update_option( 'wp_rocket_settings', [ 'wp_rocket_version' => '3.20' ] );
		}

		do_action( 'wp_rocket_first_install' );

		$this->assertSame( $expected['schedule_actions'], $this->getScheduledActionsCount() );

	}

	public function getScheduledActionsCount() : int {
		// Get container services
		$container = apply_filters( 'rocket_container', null );
		$activation = $container->get( 'pm_activation' );
		$queue = $container->get( 'pm_queue' );
		var_export($queue->get_pending_actions());
		return count( $queue->get_pending_actions() );
	}
}
