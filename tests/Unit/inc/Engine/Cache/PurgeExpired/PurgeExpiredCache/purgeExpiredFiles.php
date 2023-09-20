<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeExpired\PurgeExpiredCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache::purge_expired_files
 * @uses   \WP_Rocket\Buffer\Cache::can_generate_caching_files
 * @group  Cache
 * @group  vfs
 */
class Test_PurgeExpiredFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeExpired/PurgeExpiredCache/purgeExpiredFiles.php';

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/i18n.php';
	}

	public function testShouldReturnNullWhenNoLifespan() {
		Functions\expect( 'get_rocket_i18n_uri' )->never();
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new PurgeExpiredCache( '' );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
		$this->assertEquals( 0, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\expect( 'get_rocket_i18n_uri' )
			->once()
			->andReturn( [ null, 1, '' ] );
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new PurgeExpiredCache( '' );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );
		$this->assertEquals( 1, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );

		// Check that no files were purged, i.e. just to make sure.
		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteCacheFilesOlderThanLifespan( $expirationTime, $lifespan, $expiredFiles, $deletedDirs ) {
		$this->setFilesToExpire( $expiredFiles, $expirationTime );

		// Test the expired files exist before we purge.
		foreach ( $expiredFiles as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		Functions\expect( 'get_rocket_i18n_uri' )->once()->andReturn( [ 'http://example.org/' ] );
		Functions\expect( 'get_rocket_parse_url' )
			->once()
			->andReturnUsing(
				function( $value ) {
					return parse_url( $value );
				}
			);

		// Purge the expired files.
		$expired_cache_purge = new PurgeExpiredCache( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$expired_cache_purge->purge_expired_files( $lifespan );

		$this->assertEquals( 1, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );
		$this->assertEquals( 1, Filters\applied( 'rocket_url_no_dots' ) );
		$this->assertEquals( 1, did_action( 'rocket_before_automatic_cache_purge_dir' ) );
		$this->assertEquals( 1, did_action( 'rocket_after_automatic_cache_purge_dir' ) );
		$this->assertEquals( 1, did_action( 'rocket_after_automatic_cache_purge' ) );

		// Test the expired files were purged.
		foreach ( $expiredFiles as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Test the directories were deleted.
		foreach ( $deletedDirs as $dir ) {
			$this->assertFalse( $this->filesystem->exists( $dir ) );
		}

		// Test that non-expired files were not purged.
		foreach ( array_diff( $this->original_files, $expiredFiles ) as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	private function setFilesToExpire( $files, $expirationTime = '11 hours ago' ) {
		foreach ( $files as $filepath ) {
			$file = $this->filesystem->getFile( $filepath );
			$file->lastModified( strtotime( $expirationTime ) );
		}
	}
}
