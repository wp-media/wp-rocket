<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\SwitchToRucssBanner;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering the License category of the Dashboard sidebar with the Switch to RUCSS banner.
 *
 * Uses Render::get_dashboard_sidebar() and every License callback, as on the Dashboard.
 *
 * @group AdminOnly
 * @group CriticalPath
 */
class Test_DashboardSidebarLicense extends TestCase {
	private static $user;
	private static $pricing;
	private static $render;
	private static $user_id = 0;
	private $original_user;
	private $original_pricing;
	private $async_css = true;
	private $original_hooks = [];

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
		self::$render  = $container->get( 'settings_render' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );

		wp_set_current_user( self::$user_id );
		get_role( 'administrator' )->add_cap( 'rocket_manage_options' );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );

		// Promos and add-ons (the Imagify card is hooked by a plain admin subscriber) are out of the picture:
		// only the License category is checked.
		$this->isolateHook( 'rocket_dashboard_sidebar_promos' );
		$this->isolateHook( 'rocket_dashboard_sidebar_addons' );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		update_user_meta( self::$user_id, 'rocket_boxes', [] );
		$this->restoreHooks();
		$this->white_label = false;

		parent::tear_down();
	}

	/**
	 * Replaces a hook by an empty one, or by one keeping only the callbacks accepted by $keep.
	 *
	 * IsolateHookTrait stores one single original hook, so it cannot isolate several hooks in one test.
	 *
	 * @param string        $name Hook name.
	 * @param callable|null $keep Receives a callback and returns whether to keep it.
	 *
	 * @return void
	 */
	private function isolateHook( string $name, $keep = null ) {
		global $wp_filter;

		$original = $wp_filter[ $name ] ?? null;

		if ( ! array_key_exists( $name, $this->original_hooks ) ) {
			$this->original_hooks[ $name ] = $original;
		}

		$isolated = new \WP_Hook();

		if ( $keep && $original ) {
			foreach ( $original->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( $keep( $callback['function'] ) ) {
						$isolated->add_filter( $name, $callback['function'], $priority, $callback['accepted_args'] );
					}
				}
			}
		}

		$wp_filter[ $name ] = $isolated;
	}

	private function restoreHooks() {
		global $wp_filter;

		foreach ( $this->original_hooks as $name => $original ) {
			if ( null === $original ) {
				unset( $wp_filter[ $name ] );
			} else {
				$wp_filter[ $name ] = $original;
			}
		}

		$this->original_hooks = [];
	}

	public function async_css() {
		return $this->async_css;
	}

	private function setUserData( array $data ) {
		$this->set_reflective_property(
			json_decode( json_encode( array_merge( [ 'licence_account' => 1, 'has_auto_renew' => false ], $data ) ) ),
			'user',
			self::$user
		);
	}

	private function setPricing() {
		$this->set_reflective_property(
			json_decode(
				json_encode(
					[
						'licenses' => [
							'single'   => [ 'prices' => [ 'renewal' => [ 'is_grandfather' => 24.5, 'is_grandmother' => 24.5, 'not_grandfather' => 34.3, 'is_expired' => 39.2 ] ], 'websites' => 1 ],
							'plus'     => [ 'prices' => [ 'renewal' => [ 'is_grandfather' => 49.5, 'is_grandmother' => 49.5, 'not_grandfather' => 69.3, 'is_expired' => 79.2 ] ], 'websites' => 3 ],
							'infinite' => [ 'prices' => [ 'renewal' => [ 'is_grandfather' => 124.5, 'is_grandmother' => 124.5, 'not_grandfather' => 174.3, 'is_expired' => 199.2 ] ] ],
						],
						'renewals' => [
							'extra_days'       => 90,
							'grandfather_date' => 1567296000,
							'grandmother_date' => 315532800,
							'discount_percent' => [ 'is_grandfather' => 20, 'not_grandfather' => 0, 'is_expired' => 0 ],
						],
					]
				)
			),
			'pricing',
			self::$pricing
		);
	}

	public function testRucssAloneShouldBeShownInTheLicenseCategory() {
		$this->setUserData( [ 'licence_expiration' => strtotime( 'next year' ) ] );

		$html = self::$render->get_dashboard_sidebar();

		$this->assertSame( 1, substr_count( $html, 'wpr-Dashboard-sidebar-category' ) );
		$this->assertStringContainsString( 'wpr-rucss-switch', $html );
		$this->assertStringContainsString( 'License', $html );
	}

	public function testRucssAndExpiringSoonShouldOnlyShowExpiringSoon() {
		$this->setPricing();
		$this->setUserData( [ 'licence_expiration' => strtotime( '+10 days' ) ] );

		$html = self::$render->get_dashboard_sidebar();

		$this->assertStringContainsString( 'rocket-renewal-banner', $html );
		$this->assertStringNotContainsString( 'wpr-rucss-switch', $html );
		$this->assertSame( 1, substr_count( $html, 'wpr-Dashboard-sidebar-category' ) );
	}

	public function testRucssAndRevokedShouldOnlyShowRevoked() {
		$this->setUserData(
			[
				'licence_expiration' => strtotime( 'next year' ),
				'licence'            => [ 'is_revoked' => true ],
			]
		);

		$html = self::$render->get_dashboard_sidebar();

		$this->assertStringContainsString( 'revoked-website-banner', $html );
		$this->assertStringNotContainsString( 'wpr-rucss-switch', $html );
	}

	public function testWhiteLabelWithAsyncCssShouldRenderNothing() {
		$this->white_label = true;
		$this->setUserData( [ 'licence_expiration' => strtotime( '+10 days' ) ] );

		// Like every License banner, the RUCSS one is hidden for white label: no category, no aside content.
		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	public function testShouldRenderNothingWhenRucssIsDismissed() {
		$this->setUserData( [ 'licence_expiration' => strtotime( 'next year' ) ] );
		update_user_meta( self::$user_id, 'rocket_boxes', [ 'switch_to_rucss_notice' ] );

		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	public function testShouldRenderNothingWhenAsyncCssIsOff() {
		$this->async_css = false;
		$this->setUserData( [ 'licence_expiration' => strtotime( 'next year' ) ] );

		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	public function testShouldNotLeaveAnythingOnAdminNoticesForRucss() {
		$this->setUserData( [ 'licence_expiration' => strtotime( 'next year' ) ] );
		set_current_screen( 'settings_page_wprocket' );

		// Only the Critical Path notices (the former home of the RUCSS notice) are left on admin_notices.
		$this->isolateHook(
			'admin_notices',
			function ( $callback ) {
				return is_array( $callback ) && $callback[0] instanceof \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
			}
		);

		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		set_current_screen( 'front' );

		$this->assertStringNotContainsString( 'switch_to_rucss', $notices );
		$this->assertStringNotContainsString( 'Remove Unused CSS', $notices );
	}
}
