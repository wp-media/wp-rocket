<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Page;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering views/settings/page-sections/dashboard.php
 *
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_dismiss_box
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_DashboardView extends TestCase {
	use IsolateHookTrait;

	public function set_up() {
		parent::set_up();

		// Real subscribers render on these hooks; keep only the test callbacks.
		add_action( 'rocket_before_dashboard_content', [ $this, 'beforeDashboardContent' ] );
		add_action( 'rocket_dashboard_content', [ $this, 'dashboardContent' ] );
		$this->unregisterAllCallbacksExcept( 'rocket_before_dashboard_content', 'beforeDashboardContent' );
		$this->unregisterAllCallbacksExcept( 'rocket_dashboard_content', 'dashboardContent' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_before_dashboard_content' );
		$this->restoreWpHook( 'rocket_dashboard_content' );

		parent::tear_down();
	}

	public function beforeDashboardContent() {
		echo '';
	}

	public function dashboardContent() {
		echo '';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpectedStates( $config, $expected ) {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		if ( $config['dismissed'] ) {
			update_user_meta( $user_id, 'rocket_boxes', [ 'rocket_activation_notice' ] );
		}

		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );

		$html = $render->generate(
			'page-sections/dashboard',
			[
				'id'                      => 'dashboard',
				'title'                   => 'Dashboard',
				'rocket_insights_enabled' => false,
			]
		);

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $html );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $html );
		}
	}
}
