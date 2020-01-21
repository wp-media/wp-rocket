<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::save_cloudflare_options
 *
 * @group  Cloudflare
 */
class Test_SaveCloudflareOptions extends CloudflareTestCase {

	/**
	 * Test should clean transient when Cloudflare Addons is enabled / disabled.
	 */
	public function testShouldCleanTransient() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'delete_transient' )->once()->with( 'rocket_cloudflare_is_api_keys_valid' );

		$cloudflare_subscriber = new CloudflareSubscriber( Mockery::mock( Cloudflare::class ), $mocks['options_data'], $mocks['options'] );

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
		Functions\expect( 'delete_transient' )->never()->with( 'rocket_cloudflare_is_api_keys_valid' );

		$cloudflare_subscriber = new CloudflareSubscriber( Mockery::mock( Cloudflare::class ), $mocks['options_data'], $mocks['options'] );

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

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'is_api_keys_valid' => Mockery::mock( WP_Error::class, [
				'get_error_message' => 'Error!',
			] ),
		] );

		Functions\expect( 'delete_transient' )->once()->with( 'rocket_cloudflare_is_api_keys_valid' );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( true );
		Functions\expect( 'add_settings_error' )->once();
		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'      => 1,
			'cloudflare_email'   => '',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$value     = [
			'do_cloudflare'      => 1,
			'cloudflare_email'   => 'test@test.com',
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

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'is_api_keys_valid' => true,
		] );

		Functions\expect( 'delete_transient' )->once()->with( 'rocket_cloudflare_is_api_keys_valid' );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\expect( 'add_settings_error' )->never();
		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'      => 1,
			'cloudflare_email'   => '',
			'cloudflare_api_key' => '',
			'cloudflare_zone_id' => '',
		];
		$value     = [
			'do_cloudflare'      => 1,
			'cloudflare_email'   => 'test@test.com',
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

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'set_devmode' => Mockery::mock( WP_Error::class, [
				'get_error_message' => 'Error!',
			] ),
		] );

		$cloudflare_update_result = [
			[
				'result'  => 'error',
				'message' => '<strong>WP Rocket: </strong>Cloudflare development mode error: Error!',
			],
		];

		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_update_settings', $cloudflare_update_result );

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

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'set_devmode' => 'on',
		] );

		$cloudflare_update_result = [
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket: </strong>Cloudflare development mode on',
			],
		];
		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_update_settings', $cloudflare_update_result );

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

		$wp_error = Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'Error!' );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'set_cache_level'       => $wp_error,
			'set_minify'            => $wp_error,
			'set_rocket_loader'     => $wp_error,
			'set_browser_cache_ttl' => $wp_error,
		] );

		$cloudflare_update_result = [
			[
				'result'  => 'error',
				'message' => '<strong>WP Rocket: </strong>Cloudflare cache level error: Error!',
			],
			[
				'result'  => 'error',
				'message' => '<strong>WP Rocket: </strong>Cloudflare minification error: Error!',
			],
			[
				'result'  => 'error',
				'message' => '<strong>WP Rocket: </strong>Cloudflare rocket loader error: Error!',
			],
			[
				'result'  => 'error',
				'message' => '<strong>WP Rocket: </strong>Cloudflare browser cache error: Error!',
			],
		];

		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_update_settings', $cloudflare_update_result );

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

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'set_cache_level'       => 'aggressive',
			'set_minify'            => 'on',
			'set_rocket_loader'     => 'off',
			'set_browser_cache_ttl' => '31536000',
		] );

		$cloudflare_update_result = [
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket: </strong>Cloudflare cache level set to Standard',
			],
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket: </strong>Cloudflare minification on',
			],
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket: </strong>Cloudflare rocket loader off',
			],
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket: </strong>Cloudflare browser cache set to 31536000 seconds',
			],
		];

		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_update_settings', $cloudflare_update_result );

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
}
