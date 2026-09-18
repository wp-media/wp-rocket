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
 * Test double recording every call to the protected set_cookie() seam instead of invoking
 * the real setcookie(), which is not inspectable under PHPUnit's CLI SAPI.
 */
class SetCookieRecordingUserCacheKeySubscriber extends UserCacheKeySubscriber {
	public $calls = [];

	protected function set_cookie( string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly ): void {
		$this->calls[] = compact( 'name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly' );
	}
}

/**
 * Test class covering \WP_Rocket\Engine\Cache\UserCacheKeySubscriber::set_user_cache_cookie
 *
 * @group Cache
 */
class Test_SetUserCacheCookie extends TestCase {

	const SECRET   = 'supersecretcachekey';
	const USERNAME = 'john';

	protected $options;
	protected $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new SetCookieRecordingUserCacheKeySubscriber( $this->options );

		Functions\when( 'is_ssl' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		$this->stubWpParseUrl();
	}

	public function testShouldNotSetCookieWhenCacheLoggedUserDisabled() {
		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user' )
			->andReturn( 0 );

		$this->subscriber->set_user_cache_cookie( self::USERNAME . '|1234|token|hmac', 0, time() + 3600, 1, 'logged_in', 'token' );

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldNotSetCookieWhenSecretCacheKeyIsEmpty() {
		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user' )
			->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
			->with( 'secret_cache_key' )
			->andReturn( '' );

		$this->subscriber->set_user_cache_cookie( self::USERNAME . '|1234|token|hmac', 0, time() + 3600, 1, 'logged_in', 'token' );

		$this->assertSame( [], $this->subscriber->calls );
	}

	public function testShouldSetCookieWithExpirationBoundSignatureWhenEnabled() {
		$expiration = time() + 3600;

		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user' )
			->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
			->with( 'secret_cache_key' )
			->andReturn( self::SECRET );

		$this->subscriber->set_user_cache_cookie( self::USERNAME . '|1234|token|hmac', 0, $expiration, 1, 'logged_in', 'token' );

		$expected_value = $expiration . '|' . hash_hmac( 'sha256', self::USERNAME . '|' . $expiration, self::SECRET );

		// COOKIEPATH/SITECOOKIEPATH are process-wide constants that may already have been
		// defined (with different values) by another test file, so the expected number of
		// calls is derived from their actual values rather than assumed.
		$expected_calls = COOKIEPATH === SITECOOKIEPATH ? 1 : 2;

		$this->assertCount( $expected_calls, $this->subscriber->calls );

		foreach ( $this->subscriber->calls as $call ) {
			$this->assertSame( 'wp_rocket_ucc_' . COOKIEHASH, $call['name'] );
			$this->assertSame( $expected_value, $call['value'] );
			$this->assertSame( 0, $call['expire'] );
		}
	}
}
