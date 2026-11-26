<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Render;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar for the Quick Actions category.
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_GetDashboardSidebarQuickActionsView extends TestCase {
	private const HOOKS = [
		'rocket_dashboard_sidebar_license',
		'rocket_dashboard_sidebar_promos',
		'rocket_dashboard_sidebar_addons',
		'rocket_dashboard_sidebar_quick_actions',
	];

	private static $render;
	private $original_hooks = [];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container    = apply_filters( 'rocket_container', null );
		self::$render = $container->get( 'settings_render' );
	}

	public function set_up() {
		parent::set_up();

		// Only the actions added by this test: each hook is swapped for an empty one.
		global $wp_filter;

		foreach ( self::HOOKS as $hook ) {
			$this->original_hooks[ $hook ] = $wp_filter[ $hook ] ?? null;
			$wp_filter[ $hook ]            = new \WP_Hook();
		}
	}

	public function tear_down() {
		global $wp_filter;

		foreach ( $this->original_hooks as $hook => $original ) {
			if ( null === $original ) {
				unset( $wp_filter[ $hook ] );
			} else {
				$wp_filter[ $hook ] = $original;
			}
		}

		$this->original_hooks = [];

		parent::tear_down();
	}

	public function testShouldRenderNothingWithoutActions() {
		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderTheActions( $config, $expected ) {
		add_filter(
			'rocket_dashboard_sidebar_quick_actions',
			function () use ( $config ) {
				return $config['actions'];
			}
		);

		$html = self::$render->get_dashboard_sidebar();

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $html );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $html );
		}
	}
}
