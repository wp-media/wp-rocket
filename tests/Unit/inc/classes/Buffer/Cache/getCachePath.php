<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::get_cache_path
 *
 * @group  Buffer
 */
class Test_GetCachePath extends TestCase {
	private $was_cookies = [];

	protected function setUp(): void {
		parent::setUp();

		$this->was_cookies = $_COOKIE;
	}

	protected function tearDown(): void {
		$_COOKIE = $this->was_cookies;

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'is_ssl' )->justReturn( false );

		$_COOKIE = $config['superglobal'] ?? $config['cookies'];

		$config_mock = Mockery::mock( Config::class );
		$config_mock->shouldReceive( 'get_config' )->andReturnUsing(
			function ( $name ) use ( $config ) {
				return 'cache_dynamic_cookies' === $name ? $config['dynamic_cookies'] : false;
			}
		);
		$config_mock->shouldReceive( 'get_host' )->andReturn( 'example.org' );

		$tests = Mockery::mock( Tests::class );
		$tests->shouldReceive( 'get_cookies' )->andReturn( $config['cookies'] );
		$tests->shouldReceive( 'has_rejected_cookie' )->andReturn( false );
		$tests->shouldReceive( 'get_clean_request_uri' )->andReturn( '/hello/' );

		$cache = new Cache( $tests, $config_mock, [ 'cache_dir_path' => '/tmp/cache' ] );

		$this->assertSame( $expected, $cache->get_cache_path() );
	}
}
