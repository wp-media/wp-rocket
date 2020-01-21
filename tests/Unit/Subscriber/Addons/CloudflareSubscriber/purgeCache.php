<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::purge_cache
 *
 * @group  Cloudflare
 */
class Test_PurgeCache extends CloudflareTestCase {

	protected function tearDown() {
		parent::tearDown();

		// Reset.
		unset( $_GET['_wpnonce'] );
	}

	/**
	 * Test should not Purge Cloudflare if Cloudflare addon is disabled.
	 */
	public function testShouldNotPurgeWhenCloudflareIsDisabled() {
		$mocks = $this->getConstructorMocks( 0, '', '', '' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'purge_cloudflare' );

		$_GET['_wpnonce'] = 'nonce';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should not Purge Cloudflare if Cloudflare addon is enabled but user has no permissions.
	 */
	public function testShouldNotPurgeWhenCloudflareIsEnabledNoUserPermission() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'purge_cloudflare' );

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( false );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should Purge Cloudflare and return error.
	 */
	public function testShouldPurgeWithError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'is_wp_error' )->justReturn( true );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'purge_cloudflare' => Mockery::mock( WP_Error::class, [
				'get_error_message' => 'Error!',
			] ),
		] );

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cf_purge_result = [
			'result'  => 'error',
			// translators: %s = CloudFare API return message.
			'message' => sprintf( __( '<strong>WP Rocket:</strong> %s', 'rocket' ), 'Error!' ),
		];

		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_purge_result', $cf_purge_result );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should Purge Cloudflare and return success.
	 */
	public function testShouldPurgeWithSuccess() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			 'purge_cloudflare' => true,
		] );

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cf_purge_result = [
			'result'  => 'success',
			'message' => __( '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.', 'rocket' ),
		];

		Functions\expect( 'set_transient' )
			->once()
			->with( '1_cloudflare_purge_result', $cf_purge_result );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}
}
