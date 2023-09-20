<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::clean_critical_css
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_CleanCriticalCSS extends FilesystemTestCase {
	protected      $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/cleanCriticalCss.php';
	private static $critical_css;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container          = apply_filters( 'rocket_container', null );
		self::$critical_css = $container->get( 'critical_css' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_true' ] );
	}

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_true' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteFilesFromRootFolder( $config, $deleted_files, $available_folders ) {
		$critical_css_path = "vfs://public/wp-content/cache/critical-css/{$config['blog_id']}/";

		// Test that deleted Files are available.
		foreach ( $deleted_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $file ) );
		}

		// Test that Subfolders are available.
		foreach ( $available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}

		self::$critical_css->clean_critical_css( $config['version'] );

		// Test that root files are deleted now.
		foreach ( $deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $critical_css_path . $file ) );
		}

		// Test that Subfolders are still available.
		foreach ( $available_folders as $folder ) {
			$this->assertTrue( $this->filesystem->exists( $critical_css_path . $folder ) );
		}

		if ( empty( $deleted_files ) && 1 !== $config['blog_id'] ) {
			$this->assertFalse( $this->filesystem->exists( $critical_css_path ) );
		}
	}
}
