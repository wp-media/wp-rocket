<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::set_rocket_wp_cache_define
 * @uses   ::rocket_valid_key
 * @uses   ::rocket_find_wpconfig_path
 * @uses   ::rocket_direct_filesystem
 * @uses   ::rocket_put_content
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_SetRocketWpCacheDefine extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/setRocketWpCacheDefine.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheDefine( $config, $expected ) {

	}
}
