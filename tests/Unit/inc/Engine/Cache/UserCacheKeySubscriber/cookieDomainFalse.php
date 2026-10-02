<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\UserCacheKeySubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\UserCacheKeySubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test double recording every call to the protected set_cookie() seam instead of invoking
 * the real setcookie(), which is not inspectable under PHPUnit's CLI SAPI.
 */
class CookieDomainFalseRecordingUserCacheKeySubscriber extends UserCacheKeySubscriber {
	public $calls = [];

	protected function set_cookie( string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly ): void {
		$this->calls[] = compact( 'name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly' );
	}
}

/**
 * Test class covering \WP_Rocket\Engine\Cache\UserCacheKeySubscriber::set_user_cache_cookie
 * and ::clear_user_cache_cookie when COOKIE_DOMAIN is false (WordPress < 6.6 default, or
 * `define( 'COOKIE_DOMAIN', false )` in wp-config.php).
 *
 * @group Cache
 */
class Test_CookieDomainFalse extends TestCase {

	const SECRET = 'supersecretcachekey';

	protected $options;
	protected $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->constants = [
			'COOKIEHASH'      => 'testcookiehash',
			'COOKIEPATH'      => '/',
			'SITECOOKIEPATH'  => '/blog',
			'COOKIE_DOMAIN'   => false,
			'YEAR_IN_SECONDS' => 365 * 24 * 60 * 60,
		];

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new CookieDomainFalseRecordingUserCacheKeySubscriber( $this->options );
	}

	public function testShouldSetCookieWithEmptyDomainOnLogin() {
		Functions\when( 'is_ssl' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		$this->stubWpParseUrl();

		$this->options->shouldReceive( 'get' )->with( 'cache_logged_user' )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'secret_cache_key' )->andReturn( self::SECRET );

		$this->subscriber->set_user_cache_cookie( 'john|1234|token|hmac', 0, time() + 3600, 1, 'logged_in', 'token' );

		$this->assertSame( [ '/', '/blog' ], array_column( $this->subscriber->calls, 'path' ) );
		$this->assertSame( [ '', '' ], array_column( $this->subscriber->calls, 'domain' ) );
	}

	public function testShouldClearCookieWithEmptyDomainOnLogout() {
		$this->options->shouldReceive( 'get' )->with( 'secret_cache_key' )->andReturn( self::SECRET );

		$this->subscriber->clear_user_cache_cookie();

		$this->assertSame( [ '/', '/blog' ], array_column( $this->subscriber->calls, 'path' ) );
		$this->assertSame( [ '', '' ], array_column( $this->subscriber->calls, 'domain' ) );
	}
}
