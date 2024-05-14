<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\AdminSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\AdminSubscriber::clean_minify_all
 * @uses   ::rocket_clean_minify
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_CleanMinifyAll extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/AdminSubscriber/cleanMinifyAll.php';

	private $minify_js;
	private $minify_css;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'set_minify_js' ] );
		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'set_minify_css' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinifyAll( $option, $expected ) {
		$this->minify_js  = $option['minify_js'];
		$this->minify_css = $option['minify_css'];

		add_filter( 'pre_get_rocket_option_minify_js', [ $this, 'set_minify_js' ] );
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'set_minify_css' ] );

		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		switch_theme( 'twentynineteen/style.css' );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}

	public function set_minify_js() {
		return $this->minify_js;
	}

	public function set_minify_css() {
		return $this->minify_css;
	}
}
