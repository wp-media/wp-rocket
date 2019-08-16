<?php
namespace WP_Rocket\Tests\Unit\Cache;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Cache\Expired_Cache_Purge;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream,
	org\bovigo\vfs\vfsStreamDirectory;

class TestPurgeExpiredFiles extends TestCase {
	private $cache_path;
	private $mock_fs;

	public function setUp() {
		parent::setUp();

		$structure = [
			'wp-rocket' => [
				'example.org' => [
					'index.html' => '',
					'index.html_gzip' => '',
					'about' => [
						'index.html'=> '',
						'index.html_gzip' => '',
						'index-mobile.html' => '',
						'index-mobile.html_gzip' => '',
					],
					'category' => [
						'wordpress' => [
							'index.html' => '',
							'index.html_gzip' => '',
						],
					],
					'blog' => [
						'index.html' => '',
						'index.html_gzip' => '',
					],
				],
			],
		];

		$this->cache_path = vfsStream::setup( 'cache', null, $structure );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('blog')->getChild('index.html')->lastAttributeModified( strtotime( '11 hours ago' ) );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('blog')->getChild('index.html_gzip')->lastAttributeModified( strtotime( '11 hours ago' ) );

		$this->mock_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'exists',
								'delete'
							])
							->getMock();
		$this->mock_fs->method('exists')->will($this->returnCallback('file_exists'));
		$this->mock_fs->method('delete')->will($this->returnCallback(function($file) {
			if ( @is_file( $file ) ) {
				@unlink( $file );
			} elseif ( @is_dir( $file ) ) {
				@rmdir( $file );
			}
		}));
	}

	public function testShouldReturnNullWhenNoLifespan() {
		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path->getChild( 'wp-rocket' )->url() );

		$this->assertNull( $expired_cache_purge->purge_expired_files( 0 ) );
	}

	public function testShouldReturnNullWhenNoURLs() {
		Functions\When('get_rocket_i18n_uri')->justReturn(
			[
				null,
				1,
				'',
			]
		);

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path->getChild( 'wp-rocket' )->url() );

		$this->assertNull( $expired_cache_purge->purge_expired_files( 36000 ) );
	}

	public function testShouldDeleteCacheFilesOlderThanLifespan() {
		Functions\When('get_rocket_i18n_uri')->justReturn(
			[
				'http://example.org/'
			]
		);

		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});

		Functions\When('get_rocket_parse_url')->alias( function( $value ) {
			return parse_url( $value );
		} );

		$expired_cache_purge = new Expired_Cache_Purge( $this->cache_path->getChild( 'wp-rocket' )->url() );

		$expired_cache_purge->purge_expired_files( 36000 );

		$this->assertFalse(
			$this->cache_path->getChild('wp-rocket')->getChild('example.org')->hasChild('blog')
		);
		$this->assertTrue(
			$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('about')->hasChild('index.html')
		);
	}
}