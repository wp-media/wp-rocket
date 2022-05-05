<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\AdminSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\AdminSubscriber::clean_minify_all
 * @uses   ::rocket_clean_minify
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_CleanMinify extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/AdminSubscriber/cleanMinifyAll.php';

	public function set_up(){
		parent::set_up();

		add_action('switch_theme', [$this, 'testCleanMinifyAll']);
	}

	public function tear_down(){
		parent::tear_down();

		remove_action('switch_theme', [$this, 'testCleanMinifyAll']);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinifyAll( $option, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
