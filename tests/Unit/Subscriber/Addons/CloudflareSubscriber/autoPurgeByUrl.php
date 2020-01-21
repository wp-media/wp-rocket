<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use WP_Error;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::auto_purge_by_url
 *
 * @group  Cloudflare
 */
class Test_AutoPurgeByUrl extends CloudflareTestCase {

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is disabled.
	 */
	public function testShouldNotAutoPurgeByUrlWhenCloudflareIsDisabled() {
		$mocks = $this->getConstructorMocks( 0, '', '', '' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'has_page_rule' );
		$cloudflare->shouldNotReceive( 'purge_by_url' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is enabled but user has no permissions.
	 */
	public function testShouldNotAutoPurgeByUrlWhenCloudflareIsEnabledButNoUserPermissions() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( false );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'has_page_rule' );
		$cloudflare->shouldNotReceive( 'purge_by_url' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}

	/**
	 * Test should not AutoPurge Cloudflare if Cache Everything Page Rule is not set.
	 */
	public function testShouldNotAutoPurgeByUrlWhenNoCacheEverything() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( true );

		$cloudflare = Mockery::mock( Cloudflare::class, [
			'has_page_rule' => Mockery::mock( WP_Error::class ),
		] );
		$cloudflare->shouldNotReceive( 'purge_by_url' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}

	/**
	 * Test should AutoPurge Cloudflare if Cache Everything Page Rule is set but receives an error.
	 */
	public function testShouldAutoPurgeByUrlWhenCacheEverythingButReturnError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_rocket_i18n_home_url' )->once()->with( '' )->andReturn( 'http://example.org/' );
		Functions\expect( 'get_feed_link' )->twice()->andReturn( 'http://example.org/feed/', 'http://example.org/feed/comments' );

		$wp_error = Mockery::mock( WP_Error::class );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'Error!' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldReceive( 'has_page_rule' )->with( 'cache_everything' )->andReturn( true );
		$cloudflare->shouldReceive( 'purge_by_url' )
		           ->with(
			           1,
			           [
				           '/hello-world',
				           'http://example.org/',
				           'http://example.org/feed/',
				           'http://example.org/feed/comments',
			           ],
			           ''
		           )
		           ->andReturn( $wp_error );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}

	/**
	 * Test should AutoPurge Cloudflare if Cache Everything Page Rule is set.
	 */
	public function testShouldAutoPurgeByUrlWhenCacheEverything() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\expect( 'get_rocket_i18n_home_url' )->once()->with( '' )->andReturn( 'http://example.org/' );
		Functions\expect( 'get_feed_link' )->twice()->andReturn( 'http://example.org/feed/', 'http://example.org/feed/comments' );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldReceive( 'has_page_rule' )->with( 'cache_everything' )->andReturn( true );
		$cloudflare->shouldReceive( 'purge_by_url' )
		           ->with(
			           1,
			           [
				           '/hello-world',
				           'http://example.org/',
				           'http://example.org/feed/',
				           'http://example.org/feed/comments',
			           ],
			           ''
		           )
		           ->andReturn( true );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}
}
