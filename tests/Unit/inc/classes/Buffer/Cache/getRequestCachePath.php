<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Mockery;
use ReflectionMethod;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::get_request_cache_path
 *
 * @group Buffer
 */
class Test_GetRequestCachePath extends TestCase {

	const HOST         = 'example.org';
	const COOKIE_HASH   = 'cookiehash123';
	const SECRET        = 'supersecretcachekey';
	const USERNAME      = 'john';
	const REQUEST_URI   = '/some-page/';
	const CACHE_DIR     = '/tmp/wp-rocket-cache/';

	protected $config_mock;
	protected $tests_mock;
	protected $cache;

	public function setUp(): void {
		parent::setUp();

		$this->config_mock = Mockery::mock( Config::class );
		$this->tests_mock  = Mockery::mock( Tests::class );

		$this->config_mock->shouldReceive( 'get_host' )->andReturn( self::HOST );
		$this->config_mock->shouldReceive( 'get_config' )->with( 'url_no_dots' )->andReturn( 0 );
		$this->config_mock->shouldReceive( 'get_config' )->with( 'cookie_hash' )->andReturn( self::COOKIE_HASH );
		$this->config_mock->shouldReceive( 'get_config' )->with( 'logged_in_cookie' )->andReturn( 'wordpress_logged_in_' . self::COOKIE_HASH );
		$this->config_mock->shouldReceive( 'get_config' )->with( 'secret_cache_key' )->andReturn( self::SECRET );

		$this->tests_mock->shouldReceive( 'get_clean_request_uri' )->andReturn( self::REQUEST_URI );
		$this->tests_mock->shouldReceive( 'has_rejected_cookie' )->andReturn( false );

		$this->cache = new Cache(
			$this->tests_mock,
			$this->config_mock,
			[
				'cache_dir_path' => self::CACHE_DIR,
			]
		);
	}

	protected function callGetRequestCachePath( array $cookies ) {
		$method = new ReflectionMethod( Cache::class, 'get_request_cache_path' );
		$method->setAccessible( true );

		return $method->invoke( $this->cache, $cookies );
	}

	protected function companionCookieValue( string $username, int $expiration, string $secret = self::SECRET ): string {
		return $expiration . '|' . hash_hmac( 'sha256', $username . '|' . $expiration, $secret );
	}

	protected function loggedInCookieName(): string {
		return 'wordpress_logged_in_' . self::COOKIE_HASH;
	}

	protected function companionCookieName(): string {
		return 'wp_rocket_ucc_' . self::COOKIE_HASH;
	}

	protected function expectedAnonymousPath(): string {
		return rtrim( self::CACHE_DIR, '/' ) . '/' . self::HOST . rtrim( self::REQUEST_URI, '/' );
	}

	protected function expectedPerUserPath(): string {
		$user_key = strtolower( rawurlencode( self::USERNAME ) ) . '-' . self::SECRET;

		return rtrim( self::CACHE_DIR, '/' ) . '/' . self::HOST . '-' . $user_key . rtrim( self::REQUEST_URI, '/' );
	}

	protected function expectedSharedLoggedInPath(): string {
		return rtrim( self::CACHE_DIR, '/' ) . '/' . self::HOST . '-loggedin-' . self::SECRET . rtrim( self::REQUEST_URI, '/' );
	}

	public function testShouldReturnPerUserBucketWhenCompanionCookieIsValidAndNotExpired() {
		$this->config_mock->shouldReceive( 'get_config' )->with( 'common_cache_logged_users' )->andReturn( 0 );

		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $this->companionCookieValue( self::USERNAME, time() + 3600 ),
		];

		$this->assertSame( $this->expectedPerUserPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCompanionCookieIsMissing() {
		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCompanionCookieHasWrongSecret() {
		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $this->companionCookieValue( self::USERNAME, time() + 3600, 'wrong-secret' ),
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCompanionCookieHasWrongUsername() {
		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $this->companionCookieValue( 'someone-else', time() + 3600 ),
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCompanionCookieIsExpired() {
		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $this->companionCookieValue( self::USERNAME, time() - 3600 ),
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCompanionCookieExpirationIsTampered() {
		$expiration = time() + 3600;
		$mac        = hash_hmac( 'sha256', self::USERNAME . '|' . $expiration, self::SECRET );

		// Tamper the expiration after signing: HMAC no longer matches the new expiration.
		$tampered_expiration = $expiration + 1000000;

		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $tampered_expiration . '|' . $mac,
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenCommonCacheEnabledButCompanionCookieMissing() {
		$this->config_mock->shouldReceive( 'get_config' )->with( 'common_cache_logged_users' )->andReturn( 1 );

		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
		];

		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnSharedLoggedInBucketWhenCommonCacheEnabledAndCompanionCookieValid() {
		$this->config_mock->shouldReceive( 'get_config' )->with( 'common_cache_logged_users' )->andReturn( 1 );

		$cookies = [
			$this->loggedInCookieName() => self::USERNAME . '|9999999999|token|hmac',
			$this->companionCookieName() => $this->companionCookieValue( self::USERNAME, time() + 3600 ),
		];

		$this->assertSame( $this->expectedSharedLoggedInPath(), $this->callGetRequestCachePath( $cookies ) );
	}

	public function testShouldReturnAnonymousBucketWhenNoLoggedInCookie() {
		$this->assertSame( $this->expectedAnonymousPath(), $this->callGetRequestCachePath( [] ) );
	}
}
