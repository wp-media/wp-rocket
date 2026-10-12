<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use WP_Rocket\Buffer\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::is_valid_user_cache_cookie_value
 *
 * @group Buffer
 */
class Test_IsValidUserCacheCookieValue extends TestCase {

	const SECRET   = 'supersecretcachekey';
	const USERNAME = 'john';

	/**
	 * Builds a companion cookie value with a valid HMAC for a raw (possibly malformed) expiration.
	 *
	 * @param string $expiration Raw expiration segment.
	 * @return string
	 */
	protected static function signed_value( string $expiration ): string {
		return $expiration . '|' . hash_hmac( 'sha256', self::USERNAME . '|' . $expiration, self::SECRET );
	}

	public function testShouldAcceptValidUnexpiredValue() {
		$value = Cache::get_user_cache_cookie_value( self::USERNAME, time() + HOUR_IN_SECONDS, self::SECRET );

		$this->assertTrue( Cache::is_valid_user_cache_cookie_value( $value, self::USERNAME, self::SECRET ) );
	}

	/**
	 * @dataProvider providerInvalidValues
	 */
	public function testShouldRejectInvalidValue( callable $value, string $username, string $secret ) {
		$this->assertFalse( Cache::is_valid_user_cache_cookie_value( $value(), $username, $secret ) );
	}

	public function providerInvalidValues(): array {
		$future = function (): int {
			return time() + HOUR_IN_SECONDS;
		};

		return [
			'expired'                       => [
				function () {
					return Cache::get_user_cache_cookie_value( self::USERNAME, time() - 1, self::SECRET );
				},
				self::USERNAME,
				self::SECRET,
			],
			'empty expiration'              => [
				function () {
					return self::signed_value( '' );
				},
				self::USERNAME,
				self::SECRET,
			],
			'non-numeric expiration'        => [
				function () {
					return self::signed_value( 'abc' );
				},
				self::USERNAME,
				self::SECRET,
			],
			'plus-signed expiration'        => [
				function () use ( $future ) {
					return self::signed_value( '+' . $future() );
				},
				self::USERNAME,
				self::SECRET,
			],
			'negative expiration'           => [
				function () {
					return self::signed_value( '-1' );
				},
				self::USERNAME,
				self::SECRET,
			],
			'decimal expiration'            => [
				function () use ( $future ) {
					return self::signed_value( $future() . '.5' );
				},
				self::USERNAME,
				self::SECRET,
			],
			'hex expiration'                => [
				function () {
					return self::signed_value( '0x7FFFFFFF' );
				},
				self::USERNAME,
				self::SECRET,
			],
			'leading whitespace expiration' => [
				function () use ( $future ) {
					return self::signed_value( ' ' . $future() );
				},
				self::USERNAME,
				self::SECRET,
			],
			'trailing newline expiration'   => [
				function () use ( $future ) {
					return self::signed_value( $future() . "\n" );
				},
				self::USERNAME,
				self::SECRET,
			],
			'tampered hmac'                 => [
				function () use ( $future ) {
					return $future() . '|' . str_repeat( 'a', 64 );
				},
				self::USERNAME,
				self::SECRET,
			],
			'wrong username'                => [
				function () use ( $future ) {
					return Cache::get_user_cache_cookie_value( 'jane', $future(), self::SECRET );
				},
				self::USERNAME,
				self::SECRET,
			],
			'missing separator'             => [
				function () use ( $future ) {
					return (string) $future();
				},
				self::USERNAME,
				self::SECRET,
			],
			'empty username'                => [
				function () use ( $future ) {
					return Cache::get_user_cache_cookie_value( '', $future(), self::SECRET );
				},
				'',
				self::SECRET,
			],
			'empty secret'                  => [
				function () use ( $future ) {
					return Cache::get_user_cache_cookie_value( self::USERNAME, $future(), '' );
				},
				self::USERNAME,
				'',
			],
		];
	}
}
