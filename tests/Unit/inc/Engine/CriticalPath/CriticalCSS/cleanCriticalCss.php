<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\{CriticalCSS, CriticalCSSGeneration};
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::clean_critical_css
 *
 * @group  CriticalPath
 */
class Test_CleanCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/cleanCriticalCss.php';

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteFilesFromRootFolder( $config, $deleted_files, $available_folders ) {
		Functions\when( 'get_current_blog_id' )->justReturn( $config['blog_id'] );

		$critical_css_path = "vfs://public/wp-content/cache/critical-css/{$config['blog_id']}/";

		// Test that deleted Files are available.
		foreach ( $deleted_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $file ) );
		}
		// Test that Subfolders are available.
		foreach ( $available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}

		$critical_css = new CriticalCSS(
			Mockery::mock( CriticalCSSGeneration::class ),
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);
		$critical_css->clean_critical_css( $config['version'] );

		// Test that root files are deleted now.
		foreach ( $deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $critical_css_path . $file ) );
		}
		// Test that Subfolders are still available.
		foreach ( $available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}
	}
}
