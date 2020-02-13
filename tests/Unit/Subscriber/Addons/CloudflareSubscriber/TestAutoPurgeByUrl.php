<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use Brain\Monkey\Functions;

/**
 * @group Cloudflare
 */
class TestAutoPurgeByUrl extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is disabled.
	 */
	public function testShouldNotAutoPurgeByUrlWhenCloudflareIsDisabled() {
		$mocks = $this->getConstructorMocks( 0, '', '', '' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('has_page_rule');
		$cloudflare->shouldNotReceive('purge_by_url');

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
	}

	/**
	 * Test should not AutoPurge Cloudflare if Cloudflare Addon is enabled but user has no permissions.
	 */
	public function testShouldNotAutoPurgeByUrlWhenCloudflareIsEnabledButNoUserPermissions() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		Functions\when( 'current_user_can' )->justReturn( false );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('has_page_rule');
		$cloudflare->shouldNotReceive('purge_by_url');

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
		$wp_error   = \Mockery::mock( \WP_Error::class );
		$cloudflare = \Mockery::mock( \WP_Rocket\Addons\Cloudflare\Cloudflare::class );
		$cloudflare->shouldReceive('has_page_rule')
			->andReturn( $wp_error );
		$cloudflare->shouldNotReceive('purge_by_url');

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

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'Error!' );

		$cloudflare = \Mockery::mock( \WP_Rocket\Addons\Cloudflare\Cloudflare::class );
		$cloudflare->shouldReceive('has_page_rule')->with( 'cache_everything' )->andReturn( true );
		$cloudflare->shouldReceive('purge_by_url')
		           ->with( 1, [ '/hello-world', 'http://example.org/', 'http://example.org/feed/', 'http://example.org/feed/comments' ], '' )
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

		$cloudflare = \Mockery::mock( \WP_Rocket\Addons\Cloudflare\Cloudflare::class );
		$cloudflare->shouldReceive('has_page_rule')->with( 'cache_everything' )->andReturn( true );
		$cloudflare->shouldReceive('purge_by_url')
		           ->with( 1, [ '/hello-world', 'http://example.org/', 'http://example.org/feed/', 'http://example.org/feed/comments' ], '' )
		           ->andReturn( true );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->auto_purge_by_url( 1, [ '/hello-world' ], '' );
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
