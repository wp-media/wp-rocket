<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
	define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
}

/**
 * Test class covering \WP_Rocket\Buffer\Cache::maybe_process_buffer
 *
 * @group Buffer
 */
class Test_MaybeProcessBuffer extends TestCase {

	const COOKIE_HASH = 'cookiehash123';

	public function testShouldNotWriteCacheWhenLoggedInRequestCannotBeVerified() {
		$config = Mockery::mock( Config::class );
		$tests  = Mockery::mock( Tests::class );

		$config->shouldReceive( 'get_host' )->andReturn( 'example.org' );
		$config->shouldReceive( 'get_config' )->with( 'url_no_dots' )->andReturn( 0 );
		$config->shouldReceive( 'get_config' )->with( 'cookie_hash' )->andReturn( self::COOKIE_HASH );
		$config->shouldReceive( 'get_config' )->with( 'logged_in_cookie' )->andReturn( 'wordpress_logged_in_' . self::COOKIE_HASH );
		$config->shouldReceive( 'get_config' )->with( 'secret_cache_key' )->andReturn( 'supersecretcachekey' );

		$tests->shouldReceive( 'can_process_buffer' )->andReturn( true );
		$tests->shouldReceive( 'get_clean_request_uri' )->andReturn( '/some-page/' );
		$tests->shouldReceive( 'has_rejected_cookie' )->andReturn( false );
		$tests->shouldReceive( 'get_raw_request_uri' )->andReturn( '/some-page/' );
		// Logged-in cookie without a companion cookie: WordPress rendered the page as that user.
		$tests->shouldReceive( 'get_cookies' )->andReturn(
			[
				'wordpress_logged_in_' . self::COOKIE_HASH => 'john|9999999999|token|hmac',
			]
		);

		Functions\expect( 'rocket_mkdir_p' )->never();

		$cache  = new Cache( $tests, $config, [ 'cache_dir_path' => '/tmp/wp-rocket-cache/' ] );
		$buffer = '<html><body>Howdy, john</body></html>';

		$result = $cache->maybe_process_buffer( $buffer );

		$this->assertStringStartsWith( $buffer, $result );
	}
}
