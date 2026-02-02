<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::maybe_add_revoked_bubble
 *
 * @group License
 * @group AdminOnly
 */
class MaybeAddRevokedBubble extends TestCase {
	private static $user;
	private $original_user;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_menu_title', 'maybe_add_revoked_bubble' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_menu_title' );

		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );

        if ( true === $config['current_user_can'] ) {
			$this->createUser( 'administrator' );
			$this->assertTrue( current_user_can( 'rocket_manage_options' ) );
		} else {
			$this->createUser( 'contributor' );
			$this->assertFalse( current_user_can( 'rocket_manage_options' ) );
		}

		if ( isset( $config['white_label'] ) && $config['white_label'] ) {
			$this->white_label = true;
		}

		$this->assertSame( $expected, apply_filters( 'rocket_menu_title', 'WP Rocket' ) );
	}

    private function createUser( $role ) {
		if ( 'administrator' === $role ) {
			$admin = get_role( 'administrator' );
			$admin->add_cap( 'rocket_manage_options' );
		}

		$user = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user );
	}
}
