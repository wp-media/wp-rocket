<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WPDieException;
use function WP_Rocket\Tests\getTestsRootDir;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::purge_cdn_cache
 * @group  RocketCDN
 */
class Test_PurgeCdnCache extends TestCase {
	private $api_client;
	private $page;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WPDieException.php';
	}

	public function setUp() {
		parent::setUp();

		unset( $_GET['_wpnonce'] );

		$this->api_client = Mockery::mock( 'WP_Rocket\Engine\CDN\RocketCDN\APIClient' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			Mockery::mock( 'WP_Rocket\Admin\Options_Data' ),
			Mockery::mock( 'WP_Rocket\Engine\Admin\Beacon\Beacon' ),
			''
		);
	}

	/**
	 * Test should display "Something went wrong" message (via wp_nonce_asy) when the nonce is missing.
	 */
	public function testShouldWPNonceAysWhenNonceIsMissing() {
		Functions\expect( 'wp_nonce_ays' )->once()->andReturnUsing( function() {
			throw new WPDieException;
		} );
		Functions\expect( 'wp_verify_nonce' )->never();
		Functions\expect( 'current_user_can' )->never();

		$this->expectException( 'WPDieException' );
		$this->page->purge_cdn_cache();
	}

	/**
	 * Test should display "Something went wrong" message (via wp_nonce_asy) when the nonce is invalid.
	 */
	public function testShouldWPNonceAysWhenNonceInvalid() {
		Functions\expect( 'wp_nonce_ays' )->once()->andReturnUsing( function() {
			throw new WPDieException;
		} );
		Functions\expect( 'wp_verify_nonce' )->once()->with( 'invalid', 'rocket_purge_rocketcdn' )->andReturn( false );
		Functions\expect( 'current_user_can' )->never();

		$_GET['_wpnonce'] = 'invalid';

		$this->expectException( WPDieException::class );
		$this->page->purge_cdn_cache();
	}

	/**
	 * Test should wp_die() when the current user doesn't have 'rocket_manage_options' capability.
	 */
	public function testShouldWPDieWhenCurrentUserCant() {
		Functions\expect( 'wp_nonce_ays' )->never();
		Functions\expect( 'wp_verify_nonce' )->once()->with( 'valid', 'rocket_purge_rocketcdn' )->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( false );
		Functions\expect( 'wp_die' )->once()->andReturnUsing( function() {
			throw new WPDieException;
		} );

		$_GET['_wpnonce'] = 'valid';

		$this->expectException( WPDieException::class );
		$this->page->purge_cdn_cache();
	}

	/**
	 * Test should set the transient and redirect when the current user does have 'rocket_manage_options' capability.
	 */
	public function testSetTransientAndRedirectWhenCurrentUserCan() {
		Functions\expect( 'wp_nonce_ays' )->never();
		Functions\expect( 'wp_verify_nonce' )->once()->with( 'valid', 'rocket_purge_rocketcdn' )->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( true );

		$response = [
			'status'  => 'success',
			'message' => 'RocketCDN cache purge successful.',
		];
		$this->api_client->shouldReceive( 'purge_cache_request' )
		                 ->andReturn( $response );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'rocketcdn_purge_cache_response', $response, 3600 );

		Functions\expect( 'wp_get_referer' )->once();
		Functions\expect( 'esc_url_raw' )->once();
		Functions\expect( 'wp_safe_redirect' )->once();
		Functions\expect( 'wp_die' )->once();

		$_GET['_wpnonce'] = 'valid';
		$this->page->purge_cdn_cache();
	}
}
