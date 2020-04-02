<?php

namespace WP_Rocket\Tests\Unit\inc\classes\optimization\CSS\Critical_CSS;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Optimization\CSS\Critical_CSS;
use WP_Rocket\Optimization\CSS\Critical_CSS_Generation;

/**
 * @covers \WP_Rocket\Optimization\CSS\Critical_CSS::clean_critical_css
 * @group  CPCSS
 */
class Test_CleanCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/classes/optimization/CSS/Critical_CSS/cleanCriticalCss.php';

	private $critical_css;
	private $critical_css_path;
	private $critical_css_generation;

	public function setUp() {
		parent::setUp();

		$this->critical_css_path       = 'wp-content/cache/critical-css/';
		$this->critical_css_generation = Mockery::mock( Critical_CSS_Generation::class );

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( $this->critical_css_path ) );
		Functions\expect( 'home_url' )->with( '/' )->andReturn( 'http://example.org' );
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

		$this->critical_css = new Critical_CSS( $this->critical_css_generation );
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
