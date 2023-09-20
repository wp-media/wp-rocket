<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::add_notification_bubble
 *
 * @group License
 * @group AdminOnly
 */
class AddNotificationBubble extends TestCase {
	private static $user;
	private static $pricing;
	private $original_user;
	private $original_pricing;
	private static $user_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		delete_transient( 'rocket_promo_seen_' . self::$user_id );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $title, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );
		$this->set_reflective_property( $config['pricing'], 'pricing', self::$pricing );

		if ( false !== $config['transient'] ) {
			set_transient( 'rocket_promo_seen_' . self::$user_id, 1, MINUTE_IN_SECONDS );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_menu_title', $title )
		);
	}
}
