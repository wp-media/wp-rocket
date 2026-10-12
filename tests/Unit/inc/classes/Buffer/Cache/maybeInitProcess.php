<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Mockery;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::maybe_init_process
 *
 * @group Buffer
 */
class Test_MaybeInitProcess extends TestCase {

	const COOKIE_HASH = 'cookiehash123';

	public function testShouldNotStartBufferWhenLoggedInRequestCannotBeVerified() {
		$config = Mockery::mock( Config::class );
		$tests  = Mockery::mock( Tests::class );

		$config->shouldReceive( 'get_host' )->andReturn( 'example.org' );
		$config->shouldReceive( 'get_config' )->with( 'url_no_dots' )->andReturn( 0 );
		$config->shouldReceive( 'get_config' )->with( 'permalink_structure' )->andReturn( '/%postname%/' );
		$config->shouldReceive( 'get_config' )->with( 'cookie_hash' )->andReturn( self::COOKIE_HASH );
		$config->shouldReceive( 'get_config' )->with( 'logged_in_cookie' )->andReturn( 'wordpress_logged_in_' . self::COOKIE_HASH );
		$config->shouldReceive( 'get_config' )->with( 'secret_cache_key' )->andReturn( 'supersecretcachekey' );

		$tests->shouldReceive( 'can_init_process' )->andReturn( true );
		$tests->shouldReceive( 'get_request_uri_base' )->andReturn( '/some-page/' );
		$tests->shouldReceive( 'get_clean_request_uri' )->andReturn( '/some-page/' );
		$tests->shouldReceive( 'has_rejected_cookie' )->andReturn( false );
		$tests->shouldReceive( 'get_raw_request_uri' )->andReturn( '/some-page/' );
		$tests->shouldReceive( 'get_cookies' )->andReturn(
			[
				'wordpress_logged_in_' . self::COOKIE_HASH => 'john|9999999999|token|hmac',
			]
		);

		$cache = new Cache( $tests, $config, [ 'cache_dir_path' => '/tmp/wp-rocket-cache/' ] );
		$level = ob_get_level();

		$cache->maybe_init_process();

		$this->assertSame( $level, ob_get_level() );
	}
}
