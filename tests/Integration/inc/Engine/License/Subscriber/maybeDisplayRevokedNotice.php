<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::maybe_display_revoked_notice
 *
 * @group License
 * @group AdminOnly
 */
class MaybeDisplayRevokedNotice extends TestCase {
	private static $user;
	private $original_user;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_revoked_notice' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );

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
		
        set_current_screen( $config['current_screen'] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
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
