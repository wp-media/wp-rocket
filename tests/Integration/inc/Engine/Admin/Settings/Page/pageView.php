<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering views/settings/page.php
 *
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_valid_key
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_PageView extends TestCase {
	use IsolateHookTrait;

	public function set_up() {
		parent::set_up();

		// Real subscribers render the License banner on this hook; keep only the test callback.
		add_filter( 'rocket_dashboard_sidebar_license', [ $this, 'sidebarLicense' ] );
		$this->unregisterAllCallbacksExcept( 'rocket_dashboard_sidebar_license', 'sidebarLicense' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_dashboard_sidebar_license' );

		parent::tear_down();
	}

	public function sidebarLicense() {
		return '<i id="sidebar-hook"></i>';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpectedStates( $config, $expected ) {
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );

		$html = $render->generate(
			'page',
			[
				'slug'            => 'wp_rocket',
				'btn_submit_text' => 'Save Changes',
				'tools_page'      => [],
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
