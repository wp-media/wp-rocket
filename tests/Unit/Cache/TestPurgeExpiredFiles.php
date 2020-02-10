<?php

namespace WP_Rocket\Tests\Unit\Cache;

use Brain\Monkey\Functions;
use WP_Rocket\Cache\Expired_Cache_Purge;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

/**
 * @group Cache
 */
class TestPurgeExpiredFiles extends VirtualFilesystemTestCase {
	protected $wprocket_structure = [
		'example.org'                             => [
			'index.html'      => '',
			'index.html_gzip' => '',
			'about'           => [
				'index.html'             => '',
				'index.html_gzip'        => '',
				'index-mobile.html'      => '',
				'index-mobile.html_gzip' => '',
			],
			'category'        => [
				'wordpress' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
			],
			'blog'            => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
			'en'              => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
		],
		'example.org-Greg-594d03f6ae698691165999' => [
			'index.html'      => '',
			'index.html_gzip' => '',
		],
	];
	private $cache_path;

	protected function setUp() {
		$this->structure['wp-rocket'] = $this->wprocket_structure;
		parent::setUp();

		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
		Functions\when( 'get_rocket_parse_url' )->alias( function( $value ) {
			return parse_url( $value );
		} );

		// Set file permissions back 11 hours.
		$files = [
			'wp-rocket/example.org/blog/index.html',
			'wp-rocket/example.org/blog/index.html_gzip',
			'wp-rocket/example.org-Greg-594d03f6ae698691165999/index.html',
			'wp-rocket/example.org/en/index.html',
		];
		foreach ( $files as $filepath ) {
			$file = $this->filesystem->getFile( $filepath );
			$file->lastAttributeModified( strtotime( '11 hours ago' ) );
		}
		$this->cache_path = $this->filesystem->getUrl( 'wp-rocket' );
	}

	public function testShouldReturnNullWhenNoLifespan() {
		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [ null, 1, '' ] );
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );
		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );
	}

	public function testShouldDeleteCacheFilesOlderThanLifespan() {
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [ 'http://example.org/' ] );

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path );

		$expired_cache_purge->purge_expired_files( 36000 );

		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org/blog' ) );
		$this->assertTrue( $this->filesystem->exists( 'wp-rocket/example.org/about/index.html' ) );
		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/index.html' ) );
	}
}
