<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::clean_critical_css
 * @group  CriticalCss
 */
class Test_CleanCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/cleanCriticalCss.php';

	private $critical_css;
	private $critical_css_path;
	private $critical_css_generation;

	public function setUp() {
		parent::setUp();

		$this->critical_css_path       = 'vfs://public/wp-content/cache/critical-css/';
		$this->critical_css_generation = Mockery::mock( CriticalCSSGeneration::class );

		Functions\expect( 'home_url' )->with( '/' )->andReturn( 'http://example.org/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteFilesFromRootFolder( $blog_id, $deleted_files, $available_folders ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( $blog_id );

		$critical_css_path = $this->critical_css_path . $blog_id . '/';

		// Test that deleted Files are available.
		foreach ( $deleted_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $file ) );
		}
		// Test that Subfolders are available.
		foreach ( $available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}

		$this->critical_css = new CriticalCSS( $this->critical_css_generation );
		$this->critical_css->clean_critical_css();

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
