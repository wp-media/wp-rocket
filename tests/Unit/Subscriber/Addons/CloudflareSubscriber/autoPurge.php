<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::auto_purge
 *
 * @group  Cloudflare
 */
class Test_AutoPurge extends CloudflareTestCase {

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is disabled.
	 */
	public function testShouldNotAutoPurgeWhenCloudflareIsDisabled() {
		$mocks = $this->getConstructorMocks( 0, '', '', '' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'has_page_rule' );
		$cloudflare->shouldNotReceive( 'purge_cloudflare' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge();
	}

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is enabled but user has no permissions.
	 */
	public function testShouldNotAutoPurgeWhenCloudflareIsEnabledButNoUserPermissions() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( false );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'has_page_rule' );
		$cloudflare->shouldNotReceive( 'purge_cloudflare' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge();
	}

	/**
	 * Test should not AutoPurge Cloudflare if Cache Everything Page Rule is not set.
	 */
	public function testShouldNotAutoPurgeWhenNoCacheEverything() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'has_page_rule' => Mockery::mock( WP_Error::class )
		] );
		$cloudflare->shouldNotReceive( 'purge_cloudflare' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge();
	}

	/**
	 * Test should AutoPurge Cloudflare if Cache Everything Page Rule is set but receives an error.
	 */
	public function testShouldAutoPurgeWhenCacheEverythingButReturnError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$wp_error = Mockery::mock( WP_Error::class, [
			'get_error_message' => 'Error!'
		] );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'has_page_rule' => true,
			'purge_cloudflare' => $wp_error
		] );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge();
	}

	/**
	 * Test should AutoPurge Cloudflare if Cache Everything Page Rule is set.
	 */
	public function testShouldAutoPurgeWhenCacheEverything() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'has_page_rule' => true,
			'purge_cloudflare' => true,
		] );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge();
	}
}
