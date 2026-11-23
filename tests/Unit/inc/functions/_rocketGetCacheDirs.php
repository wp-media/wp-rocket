<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::_rocket_get_cache_dirs
 *
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test__RocketGetCacheDirs extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/_rocketGetCacheDirs.php';
	protected $mock_rocket_get_constant = false;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	protected function tearDown(): void {
		// Reset after each test.
		_rocket_get_cache_dirs( '', '', true );

		parent::tearDown();
	}

	/**
	 * @dataProvider noncachedTestData
	 */
	public function testShouldGetDirs( $config, $expected ) {
		$url_host   = array_key_exists( 'url_host', $config ) ? $config['url_host'] : '';
		$cache_path = array_key_exists( 'cache_path', $config ) ? $config['cache_path'] : '';
		$hard_reset = array_key_exists( 'hard_reset', $config ) ? $config['hard_reset'] : '';
		if ( array_key_exists( 'windows_fs', $config ) ) {
			Functions\when( '_rocket_is_windows_fs' )->justReturn( $config['windows_fs'] );
		}

		if ( ! empty( $config['windows_regex_subject'] ) ) {
			// Windows normalization invalidates VFS URLs, so exercise the regex directly.
			$normalized_cache_path = _rocket_normalize_path( $cache_path );
			$this->assertSame( $config['normalized_cache_path'], $normalized_cache_path );

			$regex = _rocket_get_cache_dir_regex( $cache_path, $url_host );
			$this->assertSame( 1, preg_match( $regex, $config['windows_regex_subject'] ) );
			$this->assertSame( 0, preg_match( $regex, $config['windows_nonmatching_subject'] ) );

			return;
		}

		if ( empty( $cache_path ) ) {
			$this->expectRocketGetConstant();
		} else {
			Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->never();
		}

		// Run it.
		$dirs = _rocket_get_cache_dirs( $url_host, $cache_path, $hard_reset );

		$this->assertSame( $expected, $dirs );
	}

	/**
	 * @dataProvider crawlOnceTestData
	 */
	public function testShouldCrawlFilesystemOnlyOnce( $url ) {
		// Run it once to cache the dirs.
		$this->expectRocketGetConstant();
		$expected = _rocket_get_cache_dirs( $url );

		// Run it again. This time it should return the cached version and not crawl the filesystem.
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->never();
		$this->assertSame( $expected, _rocket_get_cache_dirs( $url ) );
	}

	private function expectRocketGetConstant() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( 'vfs://public/wp-content/cache/wp-rocket/' );
	}

	public function noncachedTestData() {
		$this->loadConfig();

		return $this->config['test_data']['non_cached'];
	}

	public function crawlOnceTestData() {
		$this->loadConfig();

		return $this->config['test_data']['crawlOnce'];
	}
}
