<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_minify
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanMinify.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanMinified( $extensions, $expected ) {
		$this->getShouldNotCleanEntries( $expected['non_cleaned'] );

		rocket_clean_minify( $extensions );

		$this->checkCleanedIsDeleted( $expected['cleaned'], isset( $expected['dump_results'] ) );
		$this->checkNonCleanedExist( isset( $expected['dump_results'] ) );
	}
}
