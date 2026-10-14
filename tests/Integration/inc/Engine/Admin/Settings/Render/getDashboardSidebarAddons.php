<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Render;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar for the Add-ons category.
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_GetDashboardSidebarAddons extends TestCase {
	private static $render;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container    = apply_filters( 'rocket_container', null );
		self::$render = $container->get( 'settings_render' );
	}

	public function set_up() {
		parent::set_up();

		// Only the cards added by this test: no License, Promos nor real add-on card.
		$this->unregisterAllCallbacks( 'rocket_dashboard_sidebar_license' );
		$this->unregisterAllCallbacks( 'rocket_dashboard_sidebar_promos' );
		$this->unregisterAllCallbacks( 'rocket_dashboard_sidebar_addons' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_dashboard_sidebar_license' );
		$this->restoreWpHook( 'rocket_dashboard_sidebar_promos' );
		$this->restoreWpHook( 'rocket_dashboard_sidebar_addons' );

		parent::tear_down();
	}

	public function testShouldRenderNothingWithoutCards() {
		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	public function testShouldRenderTheCardsFromDataThroughOneTemplate() {
		add_filter(
			'rocket_dashboard_sidebar_addons',
			function ( $cards ) {
				$cards[] = [
					'id'           => 'first',
					'title'        => 'First card',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'list'         => [ 'One' ],
					'button_label' => 'Go',
					'button_url'   => '#page_cdn',
					'is_external'  => false,
				];
				$cards[] = [
					'id'           => 'second',
					'title'        => 'Second card',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'list'         => [ 'One' ],
					'button_label' => 'Go',
					'button_url'   => 'https://example.org',
					'is_external'  => true,
				];

				return $cards;
			}
		);

		$html = self::$render->get_dashboard_sidebar();

		$this->assertSame( 1, substr_count( $html, 'wpr-Dashboard-sidebar-category' ) );
		$this->assertSame( 2, substr_count( $html, 'class="wpr-addonCard wpr-addonCard--' ) );
		$this->assertSame( 2, substr_count( $html, '<button type="button" class="wpr-addonCard-toggle" aria-expanded="false"' ) );
		$this->assertStringContainsString( 'href="#page_cdn"', $html );
		$this->assertStringContainsString( 'target="_blank" rel="noopener noreferrer"', $html );
	}
}
