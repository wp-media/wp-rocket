<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::toggle_cta
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_ToggleCta extends AjaxTestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );
		$this->action = 'toggle_rocketcdn_cta';
	}

	public function tear_down() {
		delete_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden' );

		parent::tear_down();
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_toggle_rocketcdn_cta' ) );

		global $wp_filter;
		$obj                   = $wp_filter['wp_ajax_toggle_rocketcdn_cta'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'toggle_cta', $callback_registration['function'][1] );
	}

	/**
	 * Test should delete the user meta when the status is big.
	 */
	public function testShouldDeleteUserMetaWhenStatusIsBig() {
		add_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true );

		$_POST['nonce']  = wp_create_nonce( 'rocket-ajax' );
		$_POST['action'] = 'toggle_rocketcdn_cta';
		$_POST['status'] = 'big';

		$this->callAjaxAction();

		$this->assertFalse( (bool) get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) );
	}

	/**
	 * Test should add/update the user meta when the status is small.
	 */
	public function testShouldUpdateUserMetaWhenStatusIsSmall() {
		$_POST['nonce']  = wp_create_nonce( 'rocket-ajax' );
		$_POST['action'] = 'toggle_rocketcdn_cta';
		$_POST['status'] = 'small';

		$this->callAjaxAction();

		$this->assertTrue( (bool) get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) );
	}
}
