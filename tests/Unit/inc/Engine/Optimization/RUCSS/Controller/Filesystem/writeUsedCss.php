<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\Filesystem;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem::write_used_css
 *
 * @group RUCSS
 */
class test_WriteUsedCss extends FilesystemTestCase {
	/**
	 * Path to test data fixture.
	 *
	 * @var string
	 */
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Controller/Filesystem/writeUsedCss.php';

	/**
	 * Sets up the test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * Tests write_used_css writes file successfully.
	 *
	 * @dataProvider providerTestData
	 *
	 * @param string $hash Hash used for the filename.
	 * @param array  $file File content and expected path.
	 * @return void
	 */
	public function testShouldReturnExpected( $hash, $file ) {
		$filesystem = new Filesystem( $this->filesystem->getUrl( 'wp-content/cache/used-css/' ) );

		$this->assertTrue( $filesystem->write_used_css( $hash, $file['content'] ) );
		$this->assertTrue( $this->filesystem->exists( $file['path'] ) );
	}

	/**
	 * Tests that write_used_css returns false when the target directory exists but is not writable.
	 *
	 * Uses a mocked WP_Filesystem_Direct so we can simulate is_writable returning false
	 * without needing to manipulate vfsStream permissions.
	 *
	 * @return void
	 */
	public function testShouldReturnFalseWhenDirectoryExistsButNotWritable() {
		$hash      = 'fa2965d41f1515951de523cecb81f85e';
		$base_path = $this->filesystem->getUrl( 'wp-content/cache/used-css/' );

		// Mock the WP_Filesystem_Direct so we can control is_writable().
		$wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		// rocket_mkdir_p succeeds (directory already exists or was created).
		Functions\expect( 'rocket_mkdir_p' )->once()->andReturn( true );

		// Simulate the directory existing but not being writable.
		$wp_filesystem->shouldReceive( 'is_writable' )->once()->andReturn( false );

		$filesystem = new Filesystem( $base_path, $wp_filesystem );

		$result = $filesystem->write_used_css( $hash, 'css content' );

		$this->assertFalse( $result );
	}
}
