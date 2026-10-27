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
	private const HOOKS = [
		'rocket_dashboard_sidebar_license',
		'rocket_dashboard_sidebar_promos',
		'rocket_dashboard_sidebar_addons',
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

		// Only the cards added by this test: no License, Promos nor real add-on card.
		// IsolateHookTrait keeps one single original hook, so each hook is swapped for an empty one here.
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
		$this->assertSame( 2, substr_count( $html, 'class="wpr-notice-close wpr-icon-close wpr-addonCard-close"' ) );
		$this->assertStringContainsString( 'data-card-id="first"', $html );
		$this->assertStringContainsString( 'data-card-id="second"', $html );
		$this->assertStringContainsString( 'href="#page_cdn"', $html );
		$this->assertStringContainsString( 'target="_blank" rel="noopener noreferrer"', $html );
	}

	public function testShouldSkipTheCardsDismissedByTheCurrentUser() {
		$user_id = self::factory()->user->create();

		wp_set_current_user( $user_id );
		update_user_meta( $user_id, 'rocket_boxes', [ 'rocket_addon_card_first' ] );

		add_filter(
			'rocket_dashboard_sidebar_addons',
			function ( $cards ) {
				$cards[] = [
					'id'           => 'first',
					'title'        => 'First card',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'button_label' => 'Go',
					'button_url'   => '#page_cdn',
				];
				$cards[] = [
					'id'           => 'second',
					'title'        => 'Second card',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'button_label' => 'Go',
					'button_url'   => 'https://example.org',
				];

				return $cards;
			}
		);

		$html = self::$render->get_dashboard_sidebar();

		$this->assertSame( 1, substr_count( $html, 'class="wpr-addonCard wpr-addonCard--' ) );
		$this->assertStringNotContainsString( 'data-card-id="first"', $html );
		$this->assertStringContainsString( 'data-card-id="second"', $html );

		delete_user_meta( $user_id, 'rocket_boxes' );
	}

	public function testShouldRenderNoCloseButtonWithoutCardId() {
		add_filter(
			'rocket_dashboard_sidebar_addons',
			function ( $cards ) {
				$cards[] = [
					'title'        => 'No id',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'button_label' => 'Go',
					'button_url'   => '#page_cdn',
				];

				return $cards;
			}
		);

		$html = self::$render->get_dashboard_sidebar();

		$this->assertSame( 1, substr_count( $html, 'class="wpr-addonCard wpr-addonCard--' ) );
		$this->assertStringNotContainsString( 'wpr-addonCard-close', $html );
	}
}
