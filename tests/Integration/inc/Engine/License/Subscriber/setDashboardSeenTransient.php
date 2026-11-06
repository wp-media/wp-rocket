<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::set_dashboard_seen_transient
 *
 * @group License
 * @group AdminOnly
 */
class Test_SetDashboardSeenTransient extends TestCase {
	private static $user;
	private $original_user;
	private static $user_id = 0;
	private $ocd;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		set_current_screen( 'settings_page_wprocket' );

		$this->unregisterAllCallbacksExcept( 'admin_footer-settings_page_wprocket', 'set_dashboard_seen_transient' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		$this->restoreWpHook( 'admin_footer-settings_page_wprocket' );
		$this->restoreWpHook( 'current_screen' );

		remove_filter( 'pre_get_rocket_option_optimize_css_delivery', [ $this, 'set_ocd'] );
		delete_transient( 'wpr_dashboard_seen_' . self::$user_id );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->ocd = $config['ocd'];

		$this->set_reflective_property( $config['user'], 'user', self::$user );

		add_filter( 'pre_get_rocket_option_optimize_css_delivery', [ $this, 'set_ocd'] );

		if ( false !== $config['transient'] ) {
			set_transient( 'wpr_dashboard_seen_' . self::$user_id, 1, MINUTE_IN_SECONDS );
		}

		do_action( 'admin_footer-settings_page_wprocket' );

		$this->assertSame(
			$expected,
			get_transient( 'wpr_dashboard_seen_' . self::$user_id )
		);
	}

	public function set_ocd() {
		return $this->ocd;
	}
}
