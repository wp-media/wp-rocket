<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enable_mobile_cpcss
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_EnableMobileCpcss extends AjaxTestCase {
	protected static $use_settings_trait = true;

	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	private static $original_settings;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id    = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public function setUp() {
		parent::setUp();

		$this->action = 'rocket_enable_mobile_cpcss';
	}

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_enable_mobile_cpcss' ) );

		global $wp_filter;
		$obj                   = $wp_filter['wp_ajax_rocket_enable_mobile_cpcss'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'enable_mobile_cpcss', $callback_registration['function'][1] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $update ) {
		if ( $config['rocket_manage_options'] ) {
			$user_id = static::$admin_user_id;
		} else {
			$user_id = static::$editor_user_id;
		}

		wp_set_current_user( $user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$options = get_option( 'wp_rocket_settings' );
		$this->assertArrayNotHasKey( 'async_css_mobile', $options );

		$response = $this->callAjaxAction();

		$options = get_option( 'wp_rocket_settings' );
		if ( $config['rocket_manage_options'] ) {
			$this->assertArrayHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertTrue( $response->success );
		} else {
			$this->assertArrayNotHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertFalse( $response->success );
		}
	}
}
