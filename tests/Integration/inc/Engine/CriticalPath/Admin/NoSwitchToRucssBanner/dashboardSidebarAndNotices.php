<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\NoSwitchToRucssBanner;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class checking that the former "Switch to Remove Unused CSS" banner is gone.
 *
 * It must not show in the Dashboard sidebar nor in the admin notices, even with Load CSS Asynchronously on.
 *
 * @group AdminOnly
 * @group CriticalPath
 */
class Test_DashboardSidebarAndNotices extends TestCase {
	private static $user;
	private static $render;
	private static $user_id = 0;
	private $original_user;
	private $original_hooks = [];

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container    = apply_filters( 'rocket_container', null );
		self::$user   = $container->get( 'user' );
		self::$render = $container->get( 'settings_render' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_async_css', '__return_true' );

		wp_set_current_user( self::$user_id );
		get_role( 'administrator' )->add_cap( 'rocket_manage_options' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );

		// A valid, long license: no License banner at all.
		$this->set_reflective_property(
			json_decode( json_encode( [ 'licence_account' => 1, 'has_auto_renew' => false, 'licence_expiration' => strtotime( 'next year' ) ] ) ),
			'user',
			self::$user
		);

		// Promos and add-ons (the Imagify card is hooked by a plain admin subscriber) are out of the picture.
		$this->isolateHook( 'rocket_dashboard_sidebar_promos' );
		$this->isolateHook( 'rocket_dashboard_sidebar_addons' );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		remove_filter( 'pre_get_rocket_option_async_css', '__return_true' );
		update_user_meta( self::$user_id, 'rocket_boxes', [] );
		$this->restoreHooks();

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

	public function testShouldNotRenderTheBannerInTheDashboardSidebar() {
		$this->assertSame( '', self::$render->get_dashboard_sidebar() );
	}

	public function testShouldNotRenderTheBannerInTheAdminNotices() {
		set_current_screen( 'settings_page_wprocket' );

		// Only the Critical Path notices (the former home of the notice) are left on admin_notices.
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
		$this->assertStringNotContainsString( 'Turn on Remove Unused CSS', $notices );
	}
}
