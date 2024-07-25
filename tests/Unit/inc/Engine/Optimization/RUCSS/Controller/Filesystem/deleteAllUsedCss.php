<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\Filesystem;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem::delete_all_used_css
 *
 * @group RUCSS
 */
class test_DeleteAllUsedCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Controller/Filesystem/deleteAllUsedCss.php';

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $files ) {
		$filesystem = new Filesystem( $this->filesystem->getUrl( 'wp-content/cache/used-css/' ) );

		$filesystem->delete_all_used_css();

		foreach( $files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}
	}
}
