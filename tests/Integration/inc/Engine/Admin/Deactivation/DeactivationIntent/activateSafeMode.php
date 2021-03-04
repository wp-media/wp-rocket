<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::activate_safe_mode
 *
 * @group  DeactivationIntent
 * @group  AdminOnly
 */
class Test_ActivateSafeMode extends AjaxTestCase {
	protected static $use_settings_trait = true;

	public function setUp() {
		parent::setUp();

		$_POST['action'] = 'rocket_safe_mode';
		$this->action    = 'rocket_safe_mode';
	}

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_safe_mode' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocket_safe_mode'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'activate_safe_mode', $callback_registration['function'][1] );
	}

	public function testShouldSendErrorWhenNoCapacity() {
		$this->_setRole( 'subscriber' );

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldResetOptions( $expected ) {
		CapTrait::setAdminCap();

		$this->_setRole( 'administrator' );

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
	}
}
