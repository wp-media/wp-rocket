<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use Brain\Monkey\Functions;

class TestSaveCloudflareOptions extends TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();

		if ( ! defined('WEEK_IN_SECONDS') ) {
			define('WEEK_IN_SECONDS', 7 * 24 * 60 * 60);
		}
		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.5');
		}
	}

	/**
	 * Test should clean transient when Cloudflare Addons is enabled / disabled.
	 */
	public function testShouldCleanTransient() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'delete_transient' )->once()->with('rocket_cloudflare_is_api_keys_valid' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [ 'do_cloudflare' => 1 ];
		$value     = [ 'do_cloudflare' => 0 ];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test should NOT clean transient when Cloudflare Addons option is preserved.
	 */
	public function testShouldNotCleanTransient() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'delete_transient' )->never()->with('rocket_cloudflare_is_api_keys_valid' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [ 'do_cloudflare' => 1 ];
		$value     = [ 'do_cloudflare' => 1 ];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}


	/**
	 * Test should revalidate Cloudflare credentials with error.
	 */
	public function testShouldSaveAndRevalidateCloudflareCredentialsWithError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( true );

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'Error!' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('is_api_keys_valid')->andReturn( $wp_error );

		Functions\expect( 'delete_transient' )->once()->with('rocket_cloudflare_is_api_keys_valid' );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( true );
		Functions\expect( 'add_settings_error' )->once();
		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare' => 1,
			'cloudflare_email' => '',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$value     = [
			'do_cloudflare' => 1,
			'cloudflare_email' => 'test@test.com',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test should revalidate Cloudflare credentials with success.
	 */
	public function testShouldSaveAndRevalidateCloudflareCredentialsWithSuccess() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('is_api_keys_valid')->andReturn( true );

		Functions\expect( 'delete_transient' )->once()->with('rocket_cloudflare_is_api_keys_valid' );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\expect( 'add_settings_error' )->never();
		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare' => 1,
			'cloudflare_email' => '',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$value     = [
			'do_cloudflare' => 1,
			'cloudflare_email' => 'test@test.com',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test Cloudflare Set dev mode with error
	 */
	public function testSetDevModeWithError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( true );

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'Error!' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('set_devmode')->andReturn( $wp_error );

		$cloudflare_update_result   = [];
		$cloudflare_update_result[] = [
			'result'  => 'error',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare development mode error: %s', 'rocket' ), 'Error!' ),
		];

		Functions\expect( 'set_transient' )->once()
			->with('1_cloudflare_update_settings', $cloudflare_update_result );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'      => 1,
			'cloudflare_devmode' => 0,
		];
		$value     = [
			'do_cloudflare'      => 1,
			'cloudflare_devmode' => 1,
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test Cloudflare Set dev mode with success
	 */
	public function testSetDevModeWithSuccess() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('set_devmode')->andReturn( 'on' );

		$cloudflare_update_result   = [];
		$cloudflare_update_result[] = [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare development mode %s', 'rocket' ), 'on' ),
		];

		Functions\expect( 'set_transient' )->once()
			->with('1_cloudflare_update_settings', $cloudflare_update_result );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'      => 1,
			'cloudflare_devmode' => 0,
		];
		$value     = [
			'do_cloudflare'      => 1,
			'cloudflare_devmode' => 1,
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test Cloudflare Settings with error
	 */
	public function testSetSettingsWithError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( true );

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'Error!' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('set_cache_level')->andReturn( $wp_error );
		$cloudflare->shouldReceive('set_minify')->andReturn( $wp_error );
		$cloudflare->shouldReceive('set_rocket_loader')->andReturn( $wp_error );
		$cloudflare->shouldReceive('set_browser_cache_ttl')->andReturn( $wp_error );

		$cloudflare_update_result   = [];
		$cloudflare_update_result[] = [
			'result'  => 'error',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare cache level error: %s', 'rocket' ), 'Error!' ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'error',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare minification error: %s', 'rocket' ), 'Error!' ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'error',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare rocket loader error: %s', 'rocket' ), 'Error!' ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'error',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare browser cache error: %s', 'rocket' ), 'Error!' ),
		];

		Functions\expect( 'set_transient' )
			->once()
			->with('1_cloudflare_update_settings', $cloudflare_update_result );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$value     = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 1,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}

	/**
	 * Test Cloudflare Settings with success.
	 */
	public function testSetSettingsWithSuccess() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( false );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('set_cache_level')->andReturn( 'aggressive' );
		$cloudflare->shouldReceive('set_minify')->andReturn( 'on' );
		$cloudflare->shouldReceive('set_rocket_loader')->andReturn( 'off' );
		$cloudflare->shouldReceive('set_browser_cache_ttl')->andReturn( '31536000' );

		$cf_cache_level_return      = _x( 'Standard', 'Cloudflare caching level', 'rocket' );
		$cloudflare_update_result   = [];
		$cloudflare_update_result[] = [
			'result'  => 'success',
			// translators: %s is the caching level returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare cache level set to %s', 'rocket' ), $cf_cache_level_return ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare minification %s', 'rocket' ), 'on' ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare rocket loader %s', 'rocket' ), 'off' ),
		];
		$cloudflare_update_result[] = [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare browser cache set to %s seconds', 'rocket' ), '31536000' ),
		];

		Functions\expect( 'set_transient' )->once()
			->with('1_cloudflare_update_settings', $cloudflare_update_result );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$value     = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 1,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$cloudflare_subscriber->save_cloudflare_options( $old_value, $value );
	}


	/**
	 * Get the mocks required by Cloudflare’s constructor.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 * @access private
	 *
	 * @param integer $do_cloudflare      - Value to return for $options->get( 'do_cloudflare' ).
	 * @param string  $cloudflare_email   - Value to return for $options->get( 'cloudflare_email' ).
	 * @param string  $cloudflare_api_key - Value to return for $options->get( 'cloudflare_api_key' ).
	 * @param string  $cloudflare_zone_id - Value to return for $options->get( 'cloudflare_zone_id' ).
	 * @return array                      - Array of Mocks
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options      = $this->createMock('WP_Rocket\Admin\Options');
		$options_data = $this->createMock('WP_Rocket\Admin\Options_Data');
		$map     = [
			[
				'do_cloudflare',
				'',
				$do_cloudflare,
			],
			[
				'cloudflare_email',
				null,
				$cloudflare_email,
			],
			[
				'cloudflare_api_key',
				null,
				$cloudflare_api_key,
			],
			[
				'cloudflare_zone_id',
				null,
				$cloudflare_zone_id,
			],
		];
		$options_data->method('get')->will( $this->returnValueMap( $map ) );

		$mocks = [
			'options_data' => $options_data,
			'options'      => $options,
		];

		return $mocks;
	}
}
