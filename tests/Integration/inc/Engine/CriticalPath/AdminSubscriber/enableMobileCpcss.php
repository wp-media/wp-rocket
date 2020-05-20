<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enable_mobile_cpcss
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_EnableMobileCpcss extends AjaxTestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	private static $original_settings;
	private $old_settings = [];

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id           = $factory->user->create( [ 'role' => 'administrator' ] );
		self::$original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	public function tearDown() {
		parent::tearDown();
		delete_option( 'wp_rocket_settings' );
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$this->action   = 'rocket_enable_mobile_cpcss';
		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_enable_mobile_cpcss' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocket_enable_mobile_cpcss'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'enable_mobile_cpcss', $callback_registration['function'][1] );
	}

	public function testShouldEnableMobileCpcss() {
		$options = get_option( 'wp_rocket_settings' );
		$this->assertArrayNotHasKey( 'async_css_mobile', $options );

		$response = $this->callAjaxAction();

		$options = get_option( 'wp_rocket_settings' );
		$this->assertArrayHasKey( 'async_css_mobile', $options );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
	}
}
