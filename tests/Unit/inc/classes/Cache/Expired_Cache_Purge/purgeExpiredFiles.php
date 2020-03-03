<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Cache\Expired_Cache_Purge;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Cache\Expired_Cache_Purge;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Cache\Expired_Cache_Purge::purge_expired_files
 * @uses   \WP_Rocket\Buffer\Cache::can_generate_caching_files
 * @group  Cache
 */
class Test_PurgeExpiredFiles extends FilesystemTestCase {
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

		$this->rootVirtualUrl = $this->filesystem->getUrl( 'cache/wp-rocket' );

		// Set file permissions back 11 hours.
		foreach ( $this->expired_files as $filepath ) {
			$file = $this->filesystem->getFile( $filepath );
			$file->lastAttributeModified( strtotime( '11 hours ago' ) );
		}
	}

	public function testShouldReturnNullWhenNoLifespan() {
		Functions\expect( 'get_rocket_i18n_uri' )->never();

		$expired_cache_purge = new Expired_Cache_Purge( '' );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
		$this->assertEquals( 0, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\expect( 'get_rocket_i18n_uri' )
			->once()
			->andReturn( [ null, 1, '' ] );
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new Expired_Cache_Purge( '' );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );
		$this->assertEquals( 1, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );
	}

	public function testShouldDeleteCacheFilesOlderThanLifespan() {
		Functions\expect( 'get_rocket_i18n_uri' )->once()->andReturn( [ 'http://example.org/' ] );
		Functions\expect( 'get_rocket_parse_url' )
			->once()
			->andReturnUsing(
				function( $value ) {
					return parse_url( $value );
				}
			);
		$expired_cache_purge = new Expired_Cache_Purge( $this->rootVirtualUrl );

		// Test the expired files exist before we purge.
		foreach ( $this->expired_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$expired_cache_purge->purge_expired_files( 36000 );

		$this->assertEquals( 1, Filters\applied( 'rocket_automatic_cache_purge_urls' ) );
		$this->assertEquals( 1, Filters\applied( 'rocket_url_no_dots' ) );
		$this->assertEquals( 1, did_action( 'rocket_before_automatic_cache_purge_dir' ) );
		$this->assertEquals( 1, did_action( 'rocket_after_automatic_cache_purge_dir' ) );
		$this->assertEquals( 1, did_action( 'rocket_after_automatic_cache_purge' ) );

		// Test the expired files were purged.
		foreach ( $this->expired_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Test the blog directory was deleted.
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/blog' ) );

		// Test that non-expired files were not purged.
		foreach ( $this->non_expired_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}
}
