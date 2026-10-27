<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::dismiss_addon_card
 * @uses   \WP_Rocket\Engine\Admin\Settings\Page::dismiss_addon_card
 * @uses   \WP_Rocket\Engine\Admin\Settings\Render::get_addon_card_box_key
 *
 * @group  AdminOnly
 * @group  SettingsPage
 */
class Test_DismissAddonCard extends AjaxTestCase {
	use IsolateHookTrait;

	/**
	 * @var int
	 */
	private static $admin_id = 0;

	/**
	 * @var int
	 */
	private static $other_admin_id = 0;

	/**
	 * @var int
	 */
	private static $editor_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		self::$admin_id       = $factory->user->create( [ 'role' => 'administrator' ] );
		self::$other_admin_id = $factory->user->create( [ 'role' => 'administrator' ] );
		self::$editor_id      = $factory->user->create( [ 'role' => 'editor' ] );
	}

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_dismiss_addon_card';

		$this->unregisterAllCallbacks( 'admin_init' );
	}

	public function tear_down() {
		unset( $_POST['nonce'], $_POST['card_id'] );
		delete_user_meta( self::$admin_id, 'rocket_boxes' );
		delete_user_meta( self::$other_admin_id, 'rocket_boxes' );
		delete_user_meta( self::$editor_id, 'rocket_boxes' );

		$this->restoreWpHook( 'admin_init' );

		parent::tear_down();
	}

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_dismiss_addon_card' ) );

		global $wp_filter;
		$obj                   = $wp_filter['wp_ajax_rocket_dismiss_addon_card'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'dismiss_addon_card', $callback_registration['function'][1] );
	}

	public function testShouldDismissTheCardForTheCurrentUserOnly() {
		wp_set_current_user( self::$admin_id );

		$_POST['nonce']   = wp_create_nonce( 'rocket-ajax' );
		$_POST['card_id'] = 'imagify';

		$response = $this->callAjaxAction();

		$this->assertTrue( $response->success );
		$this->assertContains( 'rocket_addon_card_imagify', (array) get_user_meta( self::$admin_id, 'rocket_boxes', true ) );
		$this->assertNotContains( 'rocket_addon_card_imagify', (array) get_user_meta( self::$other_admin_id, 'rocket_boxes', true ) );
	}

	public function testShouldSucceedAndKeepASingleEntryWhenTheCardIsAlreadyDismissed() {
		wp_set_current_user( self::$admin_id );

		$_POST['nonce']   = wp_create_nonce( 'rocket-ajax' );
		$_POST['card_id'] = 'imagify';

		$this->callAjaxAction();
		$response = $this->callAjaxAction();

		$this->assertTrue( $response->success );
		$this->assertCount(
			1,
			array_keys( (array) get_user_meta( self::$admin_id, 'rocket_boxes', true ), 'rocket_addon_card_imagify', true )
		);
	}

	public function testShouldFailWhenUserCannotManageOptions() {
		wp_set_current_user( self::$editor_id );

		$_POST['nonce']   = wp_create_nonce( 'rocket-ajax' );
		$_POST['card_id'] = 'imagify';

		$response = $this->callAjaxAction();

		$this->assertFalse( $response->success );
		$this->assertNotContains( 'rocket_addon_card_imagify', (array) get_user_meta( self::$editor_id, 'rocket_boxes', true ) );
	}

	public function testShouldFailWhenTheNonceIsInvalid() {
		wp_set_current_user( self::$admin_id );

		$_POST['nonce']   = 'invalid';
		$_POST['card_id'] = 'imagify';

		$this->expectException( 'WPAjaxDieStopException' );
		$this->expectExceptionMessage( '-1' );

		$this->_handleAjax( $this->action );
	}

	/**
	 * @dataProvider provideInvalidIds
	 */
	public function testShouldFailWhenTheCardIdIsInvalid( $id ) {
		wp_set_current_user( self::$admin_id );

		$_POST['nonce']   = wp_create_nonce( 'rocket-ajax' );
		$_POST['card_id'] = $id;

		$response = $this->callAjaxAction();

		$this->assertFalse( $response->success );
		$this->assertEmpty( get_user_meta( self::$admin_id, 'rocket_boxes', true ) );
	}

	public function provideInvalidIds() {
		return [
			'empty'    => [ '' ],
			'too long' => [ str_repeat( 'a', 41 ) ],
		];
	}
}
