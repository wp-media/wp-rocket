<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Cache\FilesystemCache;

use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::get
 *
 * @group LazyloadCSS
 */
class Test_Get extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Common/Cache/FilesystemCache/integration/get.php';

	protected $config;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldOnlyReadInsideHostDir( $config, $expected ) {
		$cache = new FilesystemCache( 'background-css/' );

		$this->assertSame( $expected, $cache->get( $config['key'], 'default' ) );
	}
}
