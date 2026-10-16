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

		// Real subscribers (Global score) render on this hook; keep only the test callback.
		add_action( 'rocket_before_sidebar_content', [ $this, 'beforeSidebarContent' ] );
		$this->unregisterAllCallbacksExcept( 'rocket_before_sidebar_content', 'beforeSidebarContent' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_before_sidebar_content' );

		parent::tear_down();
	}

	public function beforeSidebarContent() {
		echo '<i id="sidebar-hook"></i>';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpectedStates( $config, $expected ) {
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );
		$render->set_settings( [] );
		$render->set_hidden_settings( [] );

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
