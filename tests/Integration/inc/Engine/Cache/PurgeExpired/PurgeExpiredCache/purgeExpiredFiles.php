<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeExpired\PurgeExpiredCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering PurgeExpiredCache::purge_expired_files
 * @uses   ::get_rocket_i18n_uri
 * @uses   ::rocket_direct_filesystem
 * @uses   ::get_rocket_parse_url
 * @uses   \WP_Rocket\Buffer\Cache::can_generate_caching_files
 * @group  Cache
 * @group  vfs
 */
class Test_PurgeExpiredFiles extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeExpired/PurgeExpiredCache/purgeExpiredFiles.php';

	public function testShouldReturnNullWhenNoLifespan() {
		Functions\expect( 'get_rocket_il8n_uri' )->never();
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new PurgeExpiredCache( $this->filesystem->getUrl( 'wp-content/cache/wp-rocket' ) );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\expect( 'rocket_direct_filesystem' )->never();

		add_filter( 'rocket_automatic_cache_purge_urls', '__return_empty_array' );

		$this->setFilesToExpire( $this->original_files );

		$expired_cache_purge = new PurgeExpiredCache( $this->filesystem->getUrl( 'wp-content/cache/wp-rocket' ) );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );

		// Check that no files were purged, i.e. just to make sure.
		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		// Clean up.
		remove_filter( 'rocket_automatic_cache_purge_urls', '__return_empty_array' );
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

		// Purge the expired files.
		$expired_cache_purge = new PurgeExpiredCache( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$expired_cache_purge->purge_expired_files( $lifespan );

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
