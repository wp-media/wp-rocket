<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\UserCacheKeySubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\UserCacheKeySubscriber;
use WP_Rocket\Tests\Unit\TestCase;

// The fallback values are wrapped in a non-literal expression (getenv() is never
// statically known) so static analysis cannot assume COOKIEPATH and SITECOOKIEPATH are
// always equal, matching the fact that WordPress may set them differently at runtime.
if ( ! defined( 'COOKIEHASH' ) ) {
	define( 'COOKIEHASH', getenv( 'WPR_TEST_COOKIEHASH' ) ?: 'testcookiehash' );
}
if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', getenv( 'WPR_TEST_COOKIEPATH' ) ?: '/' );
}
if ( ! defined( 'SITECOOKIEPATH' ) ) {
	define( 'SITECOOKIEPATH', getenv( 'WPR_TEST_SITECOOKIEPATH' ) ?: '/' );
}
if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', getenv( 'WPR_TEST_COOKIE_DOMAIN' ) ?: '' );
}

/**
 * Test double recording every call to the protected set_cookie() seam and letting the test
 * decide whether headers were already sent.
 */
class MaybeSetCookieRecordingUserCacheKeySubscriber extends UserCacheKeySubscriber {
	public $calls        = [];
	public $headers_sent = false;

	protected function set_cookie( string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly ): void {
		$this->calls[] = compact( 'name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly' );
	}

	protected function headers_sent(): bool {
		return $this->headers_sent;
	}
}

/**
 * Test class covering \WP_Rocket\Engine\Cache\UserCacheKeySubscriber::maybe_set_user_cache_cookie
 *
 * @group Cache
 */
class Test_MaybeSetUserCacheCookie extends TestCase {

	const SECRET   = 'supersecretcachekey';
	const USERNAME = 'john';
	const USER_ID  = 7;

	protected $options;
	protected $subscriber;
	protected $expiration;

	public function setUp(): void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new MaybeSetCookieRecordingUserCacheKeySubscriber( $this->options );
		$this->expiration = time() + 3600;

		$this->options->shouldReceive( 'get' )->with( 'cache_logged_user' )->andReturn( 1 )->byDefault();
		$this->options->shouldReceive( 'get' )->with( 'secret_cache_key' )->andReturn( self::SECRET )->byDefault();

		Functions\when( 'wp_parse_auth_cookie' )->justReturn(
			[
				'username'   => self::USERNAME,
				'expiration' => (string) $this->expiration,
				'token'      => 'token',
				'hmac'       => 'hmac',
				'scheme'     => 'logged_in',
			]
		);
		Functions\when( 'get_current_user_id' )->justReturn( self::USER_ID );
		Functions\when( 'wp_validate_auth_cookie' )->justReturn( self::USER_ID );
		Functions\when( 'is_ssl' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		$this->stubWpParseUrl();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();

		unset( $_COOKIE[ $this->cookieName() ] );
	}

	public function tearDown(): void {
		unset( $_COOKIE[ $this->cookieName() ] );

		parent::tearDown();
	}

	protected function cookieName( string $secret = self::SECRET ): string {
		return 'wp_rocket_ucc_' . COOKIEHASH . '_' . substr( hash_hmac( 'sha256', 'cookie_name', $secret ), 0, 12 );
	}

	protected function cookieValue( string $username, int $expiration, string $secret = self::SECRET ): string {
		return $expiration . '|' . hash_hmac( 'sha256', $username . '|' . $expiration, $secret );
	}

	public function testShouldSetCookieWhenAuthenticatedUserHasNoCompanionCookie() {
		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertNotEmpty( $this->subscriber->calls );

		foreach ( $this->subscriber->calls as $call ) {
			$this->assertSame( $this->cookieName(), $call['name'] );
			$this->assertSame( $this->cookieValue( self::USERNAME, $this->expiration ), $call['value'] );
			$this->assertSame( $this->expiration, $call['expire'] );
			$this->assertTrue( $call['httponly'] );
		}
	}

	public function testShouldReplaceCompanionCookieSignedForAnotherUser() {
		$_COOKIE[ $this->cookieName() ] = $this->cookieValue( 'someone-else', $this->expiration );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertNotEmpty( $this->subscriber->calls );
	}

	public function testShouldReplaceExpiredCompanionCookie() {
		$_COOKIE[ $this->cookieName() ] = $this->cookieValue( self::USERNAME, time() - 3600 );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertNotEmpty( $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenCompanionCookieIsAlreadyValid() {
		$_COOKIE[ $this->cookieName() ] = $this->cookieValue( self::USERNAME, $this->expiration );

		Functions\expect( 'wp_validate_auth_cookie' )->never();

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenCacheLoggedUserDisabled() {
		$this->options->shouldReceive( 'get' )->with( 'cache_logged_user' )->andReturn( 0 );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenHeadersAlreadySent() {
		$this->subscriber->headers_sent = true;

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenSecretCacheKeyIsEmpty() {
		$this->options->shouldReceive( 'get' )->with( 'secret_cache_key' )->andReturn( '' );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenNoLoggedInCookie() {
		Functions\when( 'wp_parse_auth_cookie' )->justReturn( false );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenLoggedInCookieIsNotValid() {
		// Forged logged-in cookie: WordPress does not authenticate it.
		Functions\when( 'wp_validate_auth_cookie' )->justReturn( false );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenCurrentUserDoesNotMatchLoggedInCookie() {
		// Current user determined by another mechanism than this logged-in cookie.
		Functions\when( 'wp_validate_auth_cookie' )->justReturn( self::USER_ID + 1 );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenNoCurrentUser() {
		Functions\when( 'get_current_user_id' )->justReturn( 0 );
		Functions\when( 'wp_validate_auth_cookie' )->justReturn( 0 );

		$this->subscriber->maybe_set_user_cache_cookie();

		$this->assertSame( [], $this->subscriber->calls );
	}
}
