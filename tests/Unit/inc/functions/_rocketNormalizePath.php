<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::_rocket_normalize_path
 * @uses  ::_rocket_is_windows_fs
 *
 * @group Files
 * @group vfs
 * @group Clean
 * @group normalize
 */
class Test__RocketNormalizePath extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/_rocketNormalizePath.php';

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Reset before starting.
		_rocket_is_windows_fs( true );
	}

	protected function tearDown(): void {
		// Reset after each test.
		_rocket_is_windows_fs( true );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldNormalizePath( $config, $expected ) {
		$path       = array_key_exists( 'path', $config ) ? $config['path'] : '';
		$escape     = array_key_exists( 'escape', $config ) ? $config['escape'] : '';
		$force      = array_key_exists( 'force', $config ) ? $config['force'] : '';
		$is_windows = array_key_exists( 'is_windows', $config ) ? $config['is_windows'] : false;

		Functions\expect( '_rocket_is_windows_fs' )
			->once()
			->with( $path )
			->andReturn( $is_windows );

		$this->assertSame( $expected, _rocket_normalize_path( $path, $escape, $force ) );
	}
}
