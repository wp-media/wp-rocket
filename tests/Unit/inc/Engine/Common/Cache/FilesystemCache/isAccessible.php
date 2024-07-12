<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::is_accessible
 */
class TestIsAccessible extends TestCase {
	protected $root_folder;
	protected $filesystem;
	protected $filesystemcache;

	public function set_up() {
		parent::set_up();

		$this->root_folder = '/background-css/';
		$this->filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		$this->filesystemcache = new FilesystemCache( $this->root_folder, $this->filesystem );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
	Functions\when('rocket_get_constant')->justReturn($config['root']);

	$this->filesystem->shouldReceive('exists')->with($expected['path'])->andReturn($config['exists']);
	if( ! $config['exists']) {
		Functions\expect('rocket_mkdir_p')->with($expected['path'], $this->filesystem);
	}
	$this->filesystem->shouldReceive('is_writable')->with($expected['path'])->andReturn($config['is_writable']);

	$this->assertSame($expected['output'], $this->filesystemcache->is_accessible());
	}
}
