<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::clean_critical_css
 *
 * @group  AdminOnly
 * @group  CriticalCss
 */
class Test_CleanCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/cleanCriticalCss.php';

	private $deleted_files = [
		'home.css',
		'front_page.css',
		'category.css',
		'post_tag.css',
		'page.css',
	];
	private $available_folders = [
		'folder',
		'folder/file.css',
	];

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_true' ] );
	}

	public function testShouldDeleteFilesFromRootFolder() {
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_true' ] );

		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/';
		// Test that deleted Files are available.
		foreach ( $this->deleted_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $file ) );
		}
		// Test that Subfolders are available.
		foreach ( $this->available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}

		$themes = wp_get_themes();
		$theme  = reset( $themes );

		// Switching theme will trigger : maybe_regenerate_cpcss() which will call clean_critical_css().
		switch_theme( $theme->get_stylesheet() );

		// Test that root files are deleted now.
		foreach ( $this->deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $critical_css_path  . $file ) );
		}
		// Test that Subfolders are still available.
		foreach ( $this->available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}
	}
}
