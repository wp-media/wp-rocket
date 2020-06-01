<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_cache_dir
 * @group Functions
 */
class Test_RocketCleanCacheDir extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketCleanCacheDir.php';

	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnValidConfigFileName( $config, $expected ) {
		rocket_clean_cache_dir();
		foreach ($expected['removed_paths'] as $path) {
			$this->assertFalse( $this->filesystem->exists( $path ) );
		}
	}

}
