<?php
namespace WP_Rocket\Tests\Unit\Cache;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Cache\Expired_Cache_Purge;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use org\bovigo\vfs\vfsStream,
	org\bovigo\vfs\vfsStreamDirectory;

/**
 * @group Cache
 */
class TestPurgeExpiredFiles extends TestCase {
	private $cache_path;
	private $mock_fs;

	public function setUp() {
		parent::setUp();

		// Force nginx for .gz
		$_SERVER['SERVER_SOFTWARE'] = 'nginx';

		$structure = [
			'wp-rocket' => [
				'example.org' => [
					'index.html' => '',
					'index.html.gz' => '',
					'about' => [
						'index.html'=> '',
						'index.html.gz' => '',
						'index-mobile.html' => '',
						'index-mobile.html.gz' => '',
					],
					'category' => [
						'wordpress' => [
							'index.html' => '',
							'index.html.gz' => '',
						],
					],
					'blog' => [
						'index.html' => '',
						'index.html.gz' => '',
					],
					'en' => [
						'index.html' => '',
						'index.html.gz' => '',
					],
				],
				'example.org-Greg-594d03f6ae698691165999' => [
					'index.html' => '',
					'index.html.gz' => '',
				],
			],
		];

		$this->cache_path = vfsStream::setup( 'cache', null, $structure );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('blog')->getChild('index.html')->lastAttributeModified( strtotime( '11 hours ago' ) );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('blog')->getChild('index.html.gz')->lastAttributeModified( strtotime( '11 hours ago' ) );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org-Greg-594d03f6ae698691165999')->getChild('index.html')->lastAttributeModified( strtotime( '11 hours ago' ) );
		$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('en')->getChild('index.html')->lastAttributeModified( strtotime( '11 hours ago' ) );

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
			$this->cache_path->getChild('wp-rocket/example.org')->hasChild('blog')
		);
		$this->assertTrue(
			$this->cache_path->getChild('wp-rocket/example.org/about')->hasChild('index.html')
		);
		$this->assertFalse(
			$this->cache_path->getChild('wp-rocket/example.org-Greg-594d03f6ae698691165999')->hasChild('index.html')
		);
	}

	public function testShouldDeleteCacheFilesOlderThanLifespanWhenMultilingual() {
		Functions\When('get_rocket_i18n_uri')->justReturn(
			[
				'http://example.org/en',
				'http://example.org/',
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
		$this->assertFalse(
			$this->cache_path->getChild('wp-rocket')->getChild('example.org-Greg-594d03f6ae698691165999')->hasChild('index.html')
		);
		$this->assertFalse(
			$this->cache_path->getChild('wp-rocket')->getChild('example.org')->getChild('en')->hasChild('index.html')
		);
	}
}
