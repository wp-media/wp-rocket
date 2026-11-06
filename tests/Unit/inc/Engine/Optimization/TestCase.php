<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected $options;

	public function setUp() : void {
		$this->default_vfs_structure = '/vfs-structure/optimizeMinify.php';

		parent::setUp();

		$this->stubGetRocketParseUrl();
		$this->stubWpParseUrl();
		$this->stubRocketRealpath();
		$this->stubfillWpBasename();

		$this->options = Mockery::mock( Options_Data::class );

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'create_rocket_uniqid' )->justReturn( 'rocket_uniqid' );

		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'get_rocket_i18n_uri' )->justReturn(
			[
				'http://en.example.org',
				'https://example.de',
			]
		);

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );
	}

	protected function assertFilesExists( $files ) {
		foreach ( $files as $file ) {
			if ( $this->skipGzCheck( $file ) ) {
				continue;
			}

			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	protected function skipGzCheck( $file ) {
		if ( function_exists( 'gzencode' ) ) {
			return false;
		}

		// If `gzencode()` function does not exist and the file is .gz, skip it.
		return ( substr( $file, -3 ) === '.gz' );
	}
}
