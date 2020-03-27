<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::activate_safe_mode
 * @group  DeactivationIntent
 * @group  AdminOnly
 */
class Test_ActivateSafeMode extends AjaxTestCase {
	public function setUp() {
		parent::setUp();

		$_POST['action'] = 'rocket_safe_mode';
		$this->action    = 'rocket_safe_mode';
	}

	/**
	 * Test that the callback is registered to the action.
	 */
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

	public function testShouldResetOptions() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );

		$this->_setRole( 'administrator' );

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );

		$expected_subset = [
			'embeds'                 => 0,
			'defer_all_js'           => 0,
			'async_css'              => 0,
			'lazyload'               => 0,
			'lazyload_iframes'       => 0,
			'lazyload_youtube'       => 0,
			'minify_css'             => 0,
			'minify_concatenate_css' => 0,
			'minify_js'              => 0,
			'minify_concatenate_js'  => 0,
			'minify_html'            => 0,
			'minify_google_fonts'    => 0,
			'cdn'                    => 0,
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}

		delete_option( 'wp_rocket_settings' );
	}
}
