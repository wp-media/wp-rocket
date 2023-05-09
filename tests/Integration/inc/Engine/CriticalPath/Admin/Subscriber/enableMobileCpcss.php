<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::enable_mobile_cpcss
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_EnableMobileCpcss extends AjaxTestCase {
	use ProviderTrait, DBTrait;
	protected static $provider_class = 'Settings';

	protected static $use_settings_trait = true;

	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();

		self::setAdminCap();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	public function set_up() {
		parent::set_up();

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
	 * @dataProvider providerTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $update ) {
		if ( $config['rocket_manage_options'] ) {
			$user_id = static::$admin_user_id;
		} else {
			$user_id = static::$editor_user_id;
		}

		if ( ! empty( $config['rocket_regenerate_critical_css'] ) ) {
			$this->addRegenerateCriticalCap();
		} else {
			$this->removeRegenerateCriticalCap();
		}

		wp_set_current_user( $user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$options = get_option( 'wp_rocket_settings' );
		$this->assertArrayNotHasKey( 'async_css_mobile', $options );

		$response = $this->callAjaxAction();

		$options = get_option( 'wp_rocket_settings' );
		if ( $update ) {
			$this->assertArrayHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertTrue( $response->success );
		} else {
			$this->assertArrayNotHasKey( 'async_css_mobile', $options );
			$this->assertObjectHasAttribute( 'success', $response );
			$this->assertFalse( $response->success );
		}
	}

	public static function removeRegenerateCriticalCap() {
		$admin = get_role( 'administrator' );
		$admin->remove_cap( 'rocket_regenerate_critical_css' );
		$admin->remove_cap( 'rocket_manage_options' );
	}

	public static function addRegenerateCriticalCap() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );
		$admin->add_cap( 'rocket_manage_options' );
	}
}
