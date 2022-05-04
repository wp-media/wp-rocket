<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_cache
 * @group Elementor
 * @group ThirdParty
 */
class Test_ClearCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/clearCache.php';

	public function tear_down() {
		delete_option( 'elementor_css_print_method' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCache( $files, $elementor_css_print_method, $actions, $after ) {
		foreach ( $files as $file ) {
			 $this->assertTrue( $this->filesystem->exists( $file ) );
		}
		
		add_option( 'elementor_css_print_method',  $elementor_css_print_method );
		foreach ( $actions as $action ) {
			 do_action( $action );
		}
		
		foreach ( $files as $file ) {
			 $this->assertSame( $after, $this->filesystem->exists( $file ) );
		}
	}
}
