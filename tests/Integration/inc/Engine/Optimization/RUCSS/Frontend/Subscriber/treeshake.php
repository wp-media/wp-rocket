<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::treeshake
 *
 * @group RUCSS
 */
class Test_Treeshake extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Frontend/Subscriber/treeshake.php';

	protected $config;

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();

		$this->unregisterAllCallbacksExcept('rocket_buffer', 'treeshake', 1000 );
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		add_filter('rocket_exclude_rucss_fonts_preload', [$this, 'exclude_fonts_preload']);
		add_filter('rocket_used_css_dir_level', [$this, 'used_css_dir_level']);
	}

	public function tear_down() {
		self::uninstallUsedCssTable();

		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		remove_filter('rocket_exclude_rucss_fonts_preload', [$this, 'exclude_fonts_preload']);
		remove_filter('rocket_used_css_dir_level', [$this, 'used_css_dir_level']);

		$this->restoreWpHook('rocket_buffer');

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;
		foreach ($config['files'] as $path => $file) {
			rocket_mkdir_p(dirname($path), $this->filesystem);
			$this->filesystem->put_contents($path, $file);
		}
		foreach ($config['rows'] as $row) {
			self::addResource($row);
		}

		$this->assertSame($expected['html'], apply_filters('rocket_buffer', $config['html']));

		foreach ($expected['rows'] as $row) {
			$this->assertTrue(self::resourceFound($row), json_encode($row) . ' not found');
		}
	}

	public function rucss() {
		return $this->config['rucss'];
	}

	public function exclude_fonts_preload() {
		return $this->config['font_excluded'];
	}

	public function used_css_dir_level() {
		return 3;
	}
}
