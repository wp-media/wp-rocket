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
	public function shouldReturnAsExpected($config, $expected) {
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

		return $queue->get_pending_actions();
	}
}
