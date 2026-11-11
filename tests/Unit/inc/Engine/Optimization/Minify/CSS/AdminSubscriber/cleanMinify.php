<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @uses   ::rocket_clean_minify
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/AdminSubscriber/cleanMinify.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $settings, $expected ) {
		if ( ! empty( $expected['cleaned'] ) ) {
			Functions\expect( 'rocket_clean_minify' )
				->once()
				->with( 'css' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}

		$subcriber = new AdminSubscriber();
		$subcriber->clean_minify( $this->config['settings'], $settings );
	}
}
