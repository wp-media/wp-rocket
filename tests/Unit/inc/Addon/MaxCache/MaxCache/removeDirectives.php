<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::remove_directives
 *
 * Works on a real file: what is being tested is a write, and the point of it is that everything the
 * add-on did not put there survives.
 *
 * @group MaxCache
 */
class TestRemoveDirectives extends TestCase {
	/**
	 * Directory standing in for the site root.
	 *
	 * @var string
	 */
	private $home;

	public function set_up() {
		parent::set_up();

		$this->home = sys_get_temp_dir() . '/maxcache-' . uniqid() . '/';

		mkdir( $this->home );
	}

	public function tear_down() {
		if ( file_exists( $this->home . '.htaccess' ) ) {
			unlink( $this->home . '.htaccess' );
		}

		rmdir( $this->home );

		parent::tear_down();
	}

	/**
	 * Checks what the file holds afterwards, and what the call reports.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   File contents before the call.
	 * @param array $expected Contents after it, and the return value.
	 *
	 * @return void
	 */
	public function testShouldLeaveExpectedFile( $config, $expected ) {
		file_put_contents( $this->home . '.htaccess', $config['file'] );

		Functions\when( 'get_home_path' )->justReturn( $this->home );

		/*
		 * A double that actually touches the disk: the point of this method is what the file holds
		 * afterwards, and it gets there through a temporary file and a move.
		 */
		$filesystem = Mockery::mock( 'WP_Filesystem_Direct' );
		$filesystem->shouldReceive( 'exists' )->andReturnUsing( 'file_exists' );
		// The read is kept only while the file it came from is the same file.
		$filesystem->shouldReceive( 'mtime' )->andReturn( 1 );
		$filesystem->shouldReceive( 'size' )->andReturn( 1 );
		$filesystem->shouldReceive( 'is_writable' )->andReturn( $config['writable'] ?? true );
		$filesystem->shouldReceive( 'get_contents' )->andReturnUsing( 'file_get_contents' );
		$filesystem->shouldReceive( 'put_contents' )->andReturnUsing(
			function ( $file, $contents ) {
				return false !== file_put_contents( $file, $contents );
			}
		);
		$filesystem->shouldReceive( 'move' )->andReturnUsing(
			function ( $from, $to ) {
				return rename( $from, $to );
			}
		);
		$filesystem->shouldReceive( 'delete' )->andReturnUsing(
			function ( $file ) {
				return ! file_exists( $file ) || unlink( $file );
			}
		);

		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );

		$maxcache = new MaxCache( Mockery::mock( Options::class ) );

		$this->assertSame( $expected['removed'], $maxcache->remove_directives() );
		$this->assertSame( $expected['file'], file_get_contents( $this->home . '.htaccess' ) );
	}
}
