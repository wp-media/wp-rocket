<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Mockery;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::get
 */
class Test_get extends TestCase {

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
		Functions\expect('get_rocket_parse_url')->with($expected['url'])->andReturn($config['parsed_url']);
		Functions\when('rocket_get_constant')->justReturn($config['root']);
		Functions\when('home_url')->justReturn($config['home_url']);

		$this->filesystem->expects()->exists($expected['path'])->andReturn($config['exists']);

		if($config['exists']) {
			$this->filesystem->expects()->get_contents($expected['path'])->andReturn($config['content']);
		}

        $this->assertSame($expected['output'], $this->filesystemcache->get($config['key'], $config['default']));
    }
}
