<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_cdn_mode
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_SaveCdnMode extends RESTfulTestCase {
	use CapTrait, DBTrait;

	private $admin_id;

	protected $config;

	protected $options_data;
	protected $options_api;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();
		self::setAdminCap();
		$this->admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->admin_id );

		$container          = apply_filters( 'rocket_container', null );
		$this->options_data = $container->get( 'options' );
		$this->options_api  = $container->get( 'options_api' );

		// Default transient: active free subscription (running, no plan_type=paid).
		// Tests that need a paid subscription override this via config['subscription'].
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'cdn_url'             => 'example1.org',
			],
			HOUR_IN_SECONDS
		);
	}

	public function tear_down() {
		wp_set_current_user( 0 );

		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['cdn_state'], $settings['cdn'], $settings['cdn_type'] );
		$this->options_api->set( 'settings', $settings );

		delete_transient( 'rocketcdn_status' );

		// Reset the shared User singleton so a banned-reseller scenario doesn't
		// leak into the next test case.
		apply_filters( 'rocket_container', null )->get( 'user' )->set_user( new \stdClass() );

		parent::tear_down();
	}

	/**
	 * Configures the shared User singleton from fixture config, e.g.:
	 * 'user' => [ 'is_reseller' => true, 'is_revoked' => true, 'ban_reason' => 'BANNED_WEBSITE' ].
	 */
	private function set_user_license( array $config ): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = ! empty( $config['is_revoked'] );
		$licence->plugin_updates_ban_reason = $config['ban_reason'] ?? '';

		$user_data              = new \stdClass();
		$user_data->licence     = $licence;
		$user_data->is_reseller = ! empty( $config['is_reseller'] );

		apply_filters( 'rocket_container', null )->get( 'user' )->set_user( $user_data );
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new \ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		if ( ! empty( $config['unauthenticated'] ) ) {
			wp_set_current_user( 0 );
		}

		// Per-case subscription override (e.g. paid plan requires plan_type=paid).
		if ( ! empty( $config['subscription'] ) ) {
			set_transient( 'rocketcdn_status', $config['subscription'], HOUR_IN_SECONDS );
		}

		// Per-case licence/reseller override (e.g. a banned reseller licence).
		if ( ! empty( $config['user'] ) ) {
			$this->set_user_license( $config['user'] );
		}

		$response = $this->doRestRequest(
			'POST',
			'/wp-rocket/v1/rocketcdn/mode',
			$config['params']
		);

		$settings = $this->options_api->get( 'settings', [] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'cdn_state_response':
					$this->assertSame( $value, $response['applied_cdn_state'] );
					$this->assertSame( $config['params']['mode'], $settings['cdn_state'] ?? null );
					break;
				case 'cdn':
					$this->assertSame( $value, $settings['cdn'] ?? null );
					break;
				case 'cdn_type':
					$this->assertSame( $value, $settings['cdn_type'] ?? null );
					break;
				case 'code':
					$this->assertSame( $value, $response['code'] );
					break;
				case 'status':
					$this->assertSame( $value, $response['data']['status'] );
					break;
			}
		}
	}
}
