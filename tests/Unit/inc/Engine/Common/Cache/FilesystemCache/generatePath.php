<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::generate_path
 */
class TestGeneratePath extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->constants['WP_ROCKET_CACHE_ROOT_PATH'] = '/var/html/wp-content/cache/';

		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'home_url' )->justReturn( $config['home_url'] );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$filesystemcache = new FilesystemCache( 'background-css/', Mockery::mock( WP_Filesystem_Direct::class ) );

		$this->assertSame( $expected, $filesystemcache->generate_path( $config['url'] ) );
	}
}
