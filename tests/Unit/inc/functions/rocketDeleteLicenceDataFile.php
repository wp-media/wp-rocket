<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	protected $structure = [
		'wp-rocket' => [
			'licence-data.php' => '',
		],
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		$this->assertTrue( $this->filesystem->exists( 'wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->filesystem->exists( 'wp-rocket/licence-data.php' ) );
	}
}
