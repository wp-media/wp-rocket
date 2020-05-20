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

	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	private static $original_settings;
	private $old_settings = [];

	public static function wpSetUpBeforeClass( $factory ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );

		//create an editor user that has the capability
		self::$admin_user_id     = $factory->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id    = $factory->user->create( [ 'role' => 'editor' ] );
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

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $update ) {
		if( $config['rocket_manage_options'] ){
			$user_id = static::$admin_user_id;
		}else{
			$user_id = static::$editor_user_id;
		}

		wp_set_current_user( $user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$options = get_option( 'wp_rocket_settings' );
		$this->assertArrayNotHasKey( 'async_css_mobile', $options );

		$response = $this->callAjaxAction();

		$options = get_option( 'wp_rocket_settings' );
		if( $config['rocket_manage_options'] ){
			$this->assertArrayHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertTrue( $response->success );
		} else {
			$this->assertArrayNotHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertFalse( $response->success );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'enableMobileCpcss' );
	}
}
