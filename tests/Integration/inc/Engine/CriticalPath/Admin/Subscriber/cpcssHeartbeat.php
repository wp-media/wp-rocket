<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use Mockery;
use WP_Error;
use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Engine\CriticalPath\ProcessorService;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::enable_mobcpcss_heartbeatile_cpcss
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_CpcssHeartbeat extends AjaxTestCase {
	use ProviderTrait;

	protected static $class_name         = 'Admin';
	protected static $use_settings_trait = true;
	protected static $processor_mock;
	protected $subscriber;
	protected $original_processor;

	private static $admin_user_id  = 0;


	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();
		self::$processor_mock     = Mockery::mock( ProcessorService::class );
		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function setUp() {
		parent::setUp();

		$this->action = 'rocket_cpcss_heartbeat';
		delete_transient( 'rocket_cpcss_generation_pending' );

		$this->mockFactory();
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css'] );
		$this->removeRoleCap( 'administrator', 'rocket_manage_options' );
		$this->removeRoleCap( 'administrator', 'rocket_regenerate_critical_css' );

		delete_transient( 'rocket_cpcss_generation_pending' );

		$this->restoreFactory();
	}

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_cpcss_heartbeat' ) );

		global $wp_filter;
		$obj                   = $wp_filter['wp_ajax_rocket_cpcss_heartbeat'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'cpcss_heartbeat', $callback_registration['function'][1] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $expected ) {

		$this->async_css = $config[ 'options' ][ 'async_css' ];
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );

		$this->setUserAndCapabilities( $config );

		if ( isset( $config['rocket_cpcss_generation_pending'] ) ) {
			set_transient( 'rocket_cpcss_generation_pending', $config['rocket_cpcss_generation_pending'], HOUR_IN_SECONDS );
		}

		$this->expectProcessGenerate( $config );

		$_POST['_nonce'] = wp_create_nonce( 'cpcss_heartbeat_nonce' );
		$response        = $this->callAjaxAction();

		if ( $expected['bailout'] ) {
			$this->assertFalse( $response->success );
		} else {
			$this->assertTrue( $response->success );
			$this->assertSame( $response->data->status, $expected[ 'data' ][ 'status' ] );
		}
	}

	private function expectProcessGenerate( $config ) {
		if ( isset( $config['process_generate'] ) ) {
			if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
				self::$processor_mock->shouldReceive( 'process_generate' )->andReturn( new WP_Error( 'error', 'error message' ) );
			} else {
				self::$processor_mock->shouldReceive( 'process_generate' )->andReturn( $config['process_generate'] );
			}
		}
	}

	protected function mockFactory() {
		$container                = apply_filters( 'rocket_container', '' );
		$this->subscriber         = $container->get( 'cpcss_admin' );

		$original_processor_ref   = $this->get_reflective_property( 'processor', $this->subscriber );
		$this->original_processor = $original_processor_ref->getValue( $this->subscriber );

		$original_processor_ref->setValue( $this->subscriber, self::$processor_mock );
	}

	protected function restoreFactory() {
		$this->set_reflective_property( $this->original_processor, 'processor', $this->subscriber );
	}

	public function setUserAndCapabilities( $config ) {
		if ( ! empty( $config['rocket_manage_options'] ) ) {
			$this->setRoleCap( 'administrator', 'rocket_manage_options' );
		}

		if ( ! empty( $config['rocket_regenerate_critical_css'] ) ) {
			$this->setRoleCap( 'administrator', 'rocket_regenerate_critical_css' );
		}

		wp_set_current_user( self::$admin_user_id );
	}

	public function async_css() {
		return $this->async_css;
	}

	protected function setRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->add_cap( $cap );
	}

	protected function removeRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->remove_cap( $cap );
	}
}
