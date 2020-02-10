<?php

namespace WP_Rocket\Tests\Integration\inc\classes\Cache\Expired_Cache_Purge;

use Brain\Monkey\Functions;
use WP_Rocket\Cache\Expired_Cache_Purge;
use WP_Rocket\Tests\Integration\VirtualFilesystemTestCase;

/**
 * @covers Expired_Cache_Purge::purge_expired_files
 * @group  Cache
 */
class Test_PurgeExpiredFiles extends VirtualFilesystemTestCase {
	private $expired_files = [
		'wp-rocket/example.org/blog/index.html',
		'wp-rocket/example.org/blog/index.html_gzip',
		'wp-rocket/example.org-Greg-594d03f6ae698691165999/index.html',
		'wp-rocket/example.org/en/index.html',
	];
	private $non_expired_files = [
		'wp-rocket/example.org/index.html',
		'wp-rocket/example.org/index.html_gzip',
		'wp-rocket/example.org/about/index.html',
		'wp-rocket/example.org/about/index.html_gzip',
		'wp-rocket/example.org/about/index-mobile.html',
		'wp-rocket/example.org/about/index-mobile.html_gzip',
		'wp-rocket/example.org/category/wordpress/index.html',
		'wp-rocket/example.org/category/wordpress/index.html_gzip',
		'wp-rocket/example.org/en/index.html_gzip',
		'wp-rocket/example.org-Greg-594d03f6ae698691165999/index.html_gzip',
	];

	public function setUp() {
		parent::setUp();

		// Set file permissions back 11 hours.
		foreach ( $this->expired_files as $filepath ) {
			$file = $this->filesystem->getFile( $filepath );
			$file->lastAttributeModified( strtotime( '11 hours ago' ) );
		}
	}

	public function testShouldReturnNullWhenNoLifespan() {
		Functions\expect( 'get_rocket_il8n_uri' )->never();
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\expect( 'rocket_direct_filesystem' )->never();

		add_filter( 'rocket_automatic_cache_purge_urls', '__return_empty_array' );

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );

		// Test that expired files were not purged.
		foreach ( $this->expired_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		// Test the blog directory was not deleted.
		$this->assertTrue( $this->filesystem->exists( 'wp-rocket/example.org/blog' ) );

		// Test that non-expired files were not purged.
		foreach ( $this->non_expired_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		// Clean up.
		remove_filter( 'rocket_automatic_cache_purge_urls', '__return_empty_array' );
	}

	public function testShouldDeleteCacheFilesOlderThanLifespan() {
		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );

		$expired_cache_purge->purge_expired_files( 36000 );

		// Test the expired files were purged.
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/blog/index.html' ) );
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/blog/index.html_gzip' ) );
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/index.html' ) );
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/en/index.html' ) );

		// Test the blog directory was deleted.
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/blog' ) );

		// Test that non-expired files were not purged.
		foreach ( $this->non_expired_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}
}
