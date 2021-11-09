<?php

declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @group Functions
 * @group Files
 * @group vfs
 * @group Clean
 */
class Test_RocketCleanHome extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanHome.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpectedFiles( $lang, $expected ) {
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		rocket_clean_home( $lang );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}

}
