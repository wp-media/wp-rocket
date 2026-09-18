<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\UserCacheKeySubscriber;

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
	define( 'SITECOOKIEPATH', getenv( 'WPR_TEST_SITECOOKIEPATH' ) ?: '/blog' );
}
if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', getenv( 'WPR_TEST_COOKIE_DOMAIN' ) ?: '' );
}
if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
	define( 'YEAR_IN_SECONDS', 365 * 24 * 60 * 60 );
}

/**
 * Test double recording every call to the protected set_cookie() seam instead of invoking
 * the real setcookie(), which is not inspectable under PHPUnit's CLI SAPI.
 */
class ClearCookieRecordingUserCacheKeySubscriber extends UserCacheKeySubscriber {
	public $calls = [];

	protected function set_cookie( string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly ): void {
		$this->calls[] = compact( 'name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly' );
	}
}

/**
 * Test class covering \WP_Rocket\Engine\Cache\UserCacheKeySubscriber::clear_user_cache_cookie
 *
 * @group Cache
 */
class Test_ClearUserCacheCookie extends TestCase {

	protected $options;
	protected $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new ClearCookieRecordingUserCacheKeySubscriber( $this->options );
	}

	public function testShouldClearCookieAtBothPathsWithPastExpiry() {
		$before = time();

		$this->subscriber->clear_user_cache_cookie();

		$this->assertCount( 2, $this->subscriber->calls );

		foreach ( $this->subscriber->calls as $call ) {
			$this->assertSame( 'wp_rocket_ucc_' . COOKIEHASH, $call['name'] );
			$this->assertSame( ' ', $call['value'] );
			$this->assertLessThan( $before, $call['expire'] );
		}

		$paths = array_column( $this->subscriber->calls, 'path' );

		$this->assertContains( COOKIEPATH, $paths );
		$this->assertContains( SITECOOKIEPATH, $paths );
	}
}
