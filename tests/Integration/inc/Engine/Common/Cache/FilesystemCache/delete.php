<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Cache\FilesystemCache;

use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::delete
 *
 * @group LazyloadCSS
 */
class Test_Delete extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Common/Cache/FilesystemCache/integration/delete.php';

	protected $config;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldOnlyDeleteInsideHostDir( $config, $expected ) {
		$cache   = new FilesystemCache( 'background-css/' );
		$before  = $this->filesystem->getListing( '' );
		$deleted = array_map( [ $this->filesystem, 'getUrl' ], $expected['deleted'] );

		$this->assertSame( $expected['output'], $cache->delete( $config['key'] ) );
		$this->assertSame( array_values( array_diff( $before, $deleted ) ), $this->filesystem->getListing( '' ) );
	}
}
