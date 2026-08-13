<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use Brain\Monkey\Functions;
use WP_Error;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::cpcss_heartbeat
 *
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @uses   \WP_Rocket\Engine\CriticalPath\Admin\Admin::cpcss_heartbeat
 * @uses   \WP_Rocket\Engine\CriticalPath\APIClient::send_generation_request
 * @uses   \WP_Rocket\Engine\CriticalPath\DataManager::delete_cache_job_id
 * @uses   \WP_Rocket\Engine\CriticalPath\DataManager::get_cache_job_id
 * @uses   \WP_Rocket\Engine\CriticalPath\APIClient::get_job_details
 * @uses   \WP_Rocket\Engine\CriticalPath\DataManager::set_cache_job_id
 * @uses   \WP_Rocket\Engine\CriticalPath\ProcessorService::process_generate
 * @uses   ::rocket_has_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class CpcssHeartbeatTest extends AjaxTestCase {
	use IsolateHookTrait;
	use ProviderTrait;
	protected static $provider_class = 'Admin';

	protected static $use_settings_trait = true;
	protected static $transients         = [
		'rocket_critical_css_generation_process_running' => null,
		'rocket_cpcss_generation_pending'                => null,
	];

	protected $async_css;
	protected $subscriber;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();
	}

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_cpcss_heartbeat';

		set_transient( 'rocket_critical_css_generation_process_running', [
			'generated' => 0,
			'total'     => 1,
			'items'     => [],
		] );
		delete_transient( 'rocket_cpcss_generation_pending' );

		$this->unregisterAllCallbacks( 'admin_init' );
	}

	public function tear_down() {
		$this->removeRoleCap( 'administrator', 'rocket_regenerate_critical_css' );

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		delete_transient( 'rocket_critical_css_generation_process_running' );
		delete_transient( 'rocket_cpcss_generation_pending' );

		$this->restoreWpHook( 'admin_init' );

		parent::tear_down();
	}

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_rocket_cpcss_heartbeat', 'cpcss_heartbeat' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateCPCSS( $config, $expected ) {
		$this->async_css = $config['options']['async_css'];
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );

		$this->setUserAndCapabilities( $config );

		if ( isset( $config['rocket_cpcss_generation_pending'] ) ) {
			set_transient( 'rocket_cpcss_generation_pending', $config['rocket_cpcss_generation_pending'], HOUR_IN_SECONDS );
		}

		$this->expectProcessGenerate( $config, $expected );

		$_POST['_nonce'] = wp_create_nonce( 'cpcss_heartbeat_nonce' );

		$this->expectException( 'WPAjaxDieContinueException' );

		$this->_handleAjax( $this->action );

		$response = json_decode( $this->_last_response );

		if ( $expected['bailout'] ) {
			$this->assertFalse( $response->success );
		} else {
			$this->assertTrue( $response->success );
			$this->assertSame( $expected['data']['status'], $response->data->status );
		}
	}

	private function expectProcessGenerate( $config, $expected ) {
		if ( ! isset( $config['process_generate'] ) || ! empty ( $expected['bailout_timeout'] ) ) {
			return;
		}
		$params = [
			'url'        => $config['rocket_cpcss_generation_pending']['front_page.css']['url'],
			'mobile'     => $config['rocket_cpcss_generation_pending']['front_page.css']['mobile'],
			'nofontface' => false,
		];

		$job_id = 999;

		if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
			Functions\expect( 'wp_remote_post' )
				->once()
				->with( APIClient::API_URL, [ 'body' => $params ] )
				->andReturn( new WP_Error( 'error', 'error_data' ) );
		} else {
			Functions\expect( 'wp_remote_post' )
				->once()
				->with( APIClient::API_URL, [ 'body' => $params ] )
				->andReturn( [ 'body' => '{"status":200,"success":true,"data":{"state":"generating","id":"' . $job_id . '"}}' ] );

			Functions\expect( 'wp_remote_get' )
				->once()
				->with( APIClient::API_URL . "{$job_id}/" )
				->andReturn( [ 'body' => json_encode( $config['process_generate'] ) ] );
		}
	}

	public function setUserAndCapabilities( $config ) {
		if ( ! empty( $config['rocket_manage_options'] ) ) {
			$this->setRoleCap( 'administrator', 'rocket_manage_options' );
		}

		if ( ! empty( $config['rocket_regenerate_critical_css'] ) ) {
			$this->setRoleCap( 'administrator', 'rocket_regenerate_critical_css' );
		}

		$this->_setRole( 'administrator' );
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
