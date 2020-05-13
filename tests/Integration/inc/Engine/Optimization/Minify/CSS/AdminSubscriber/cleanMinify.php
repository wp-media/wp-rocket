<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @uses   ::rocket_clean_minify
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Optimize
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_CleanMinify extends FilesystemTestCase {
	protected      $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/AdminSubscriber/cleanMinify.php';
	private static $original_settings;
	private $old_settings = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	public function setUp() {
		parent::setUp();

		$this->old_settings = array_merge( self::$original_settings, $this->config['settings'] );
		update_option( 'wp_rocket_settings', $this->old_settings );
	}

	public function tearDown() {
		parent::tearDown();

		delete_option( 'wp_rocket_settings' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $settings, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		update_option(
			'wp_rocket_settings',
			array_merge( $this->old_settings, $settings )
		);

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
