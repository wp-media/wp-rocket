<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::get
 */
class TestGet extends TestCase {
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
		Functions\expect('get_rocket_parse_url')->with($expected['url'])->andReturn($config['parsed_url']);
		Functions\when('rocket_get_constant')->justReturn($config['root']);
		Functions\when('home_url')->justReturn($config['home_url']);

		$this->filesystem->shouldReceive('exists')->with($expected['path'])->andReturn($config['exists']);

		if($config['exists']) {
			$this->filesystem->shouldReceive('get_contents')->with($expected['path'])->andReturn($config['content']);
		}

		$this->assertSame($expected['output'], $this->filesystemcache->get($config['key'], $config['default']));
	}
}
