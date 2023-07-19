<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Mockery;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Common\Cache\FilesystemCache::clear
 */
class Test_clear extends TestCase {

    /**
     * @var string
     */
    protected $root_folder;

    /**
     * @var WP_Filesystem_Direct
     */
    protected $filesystem;

    /**
     * @var FilesystemCache
     */
    protected $filesystemcache;

    public function set_up() {
        parent::set_up();
        $this->root_folder = '/background-css/';
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);

        $this->filesystemcache = new FilesystemCache($this->root_folder, $this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->justReturn($config['root']);
		$this->filesystem->expects()->exists($expected['path'])->andReturn($config['exists']);
		$this->configureDirDelete($config, $expected);
		$this->assertSame($expected['output'], $this->filesystemcache->clear());
    }

	protected function configureDirDelete($config, $expected) {
		if(! $config['exists'] ) {
			return;
		}
		Functions\expect('rocket_rrmdir')->andReturn($expected['path'], [], $this->filesystem);
	}
}
