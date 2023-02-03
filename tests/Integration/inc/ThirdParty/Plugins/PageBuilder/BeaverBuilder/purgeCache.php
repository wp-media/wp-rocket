<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_PurgeCache extends FilesystemTestCase
{
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/BeaverBuilder/purgeCache.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

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
