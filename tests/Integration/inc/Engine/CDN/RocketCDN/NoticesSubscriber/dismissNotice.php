<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::dismiss_notice
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_DismissNotice extends AjaxTestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$this->action = 'rocketcdn_dismiss_notice';
	}

	public function tearDown() {
		parent::tearDown();

		delete_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice' );
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocketcdn_dismiss_notice' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocketcdn_dismiss_notice'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'dismiss_notice', $callback_registration['function'][1] );
	}

	/**
	 * Test should update the user meta
	 */
    public function testShouldUpdateUserMetaWhenValid() {
		$_POST['nonce']  = wp_create_nonce( 'rocketcdn_dismiss_notice' );
		$_POST['action'] = 'rocketcdn_dismiss_notice';

		$this->callAjaxAction();

		$this->assertTrue( (bool) get_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true ) );
	}
}
