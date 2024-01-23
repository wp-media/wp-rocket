<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::add_expired_bubble
 *
 * @group License
 * @group AdminOnly
 */
class Test_AddExpiredBubble extends TestCase {
	private static $user_id;
	private static $user;
	private $original_user;
	private $ocd;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );

		$this->unregisterAllCallbacksExcept( 'rocket_menu_title', 'add_expired_bubble' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		$this->restoreWpHook( 'rocket_menu_title' );

		remove_filter( 'pre_get_rocket_option_optimize_css_delivery', [ $this, 'set_ocd'] );
		delete_transient( 'wpr_dashboard_seen_' . self::$user_id );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $title, $expected ) {
		$this->white_label = $config['white_label'];
		$this->ocd = $config['ocd'];

		$this->set_reflective_property( $config['user'], 'user', self::$user );

		add_filter( 'pre_get_rocket_option_optimize_css_delivery', [ $this, 'set_ocd'] );

		if ( false !== $config['transient'] ) {
			set_transient( 'wpr_dashboard_seen_' . self::$user_id, $config['transient'], HOUR_IN_SECONDS );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_menu_title', $title )
		);
	}

	public function set_ocd() {
		return $this->ocd;
	}
}
