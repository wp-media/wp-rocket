<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_PurgeCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/BeaverBuilder/purgeCache.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanRocketCacheDirectories( $action, $files ) {
		foreach ( $files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		do_action( $action );

		foreach ( $files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}
	}
}
