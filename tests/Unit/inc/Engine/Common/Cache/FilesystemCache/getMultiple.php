<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Mockery;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Common\Cache\FilesystemCache::getMultiple
 */
class Test_getMultiple extends TestCase {

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
		Functions\when('get_rocket_parse_url')->alias(function ($url) use ($config) {
			if(! key_exists($url, $config['parsed_url'])) {
				return;
			}
			return $config['parsed_url'][$url];
		});
		Functions\when('home_url')->justReturn($config['home_url']);

		Functions\when('rocket_get_constant')->justReturn($config['root']);

		foreach ($config['exists'] as $path => $exist) {
			$this->filesystem->expects()->exists($path)->andReturn($exist);
		}

		foreach ($config['content'] as $path => $content) {
			$this->filesystem->expects()->get_contents($path)->andReturn($content);
		}

        $this->assertSame($expected['output'], $this->filesystemcache->getMultiple($config['keys'], $config['default']));
    }
}
