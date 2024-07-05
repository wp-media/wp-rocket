<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Mockery;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::deleteMultiple
 */
class Test_deleteMultiple extends TestCase {

    /**
     * @var string
     */
    protected $root_folder;

    /**
     * @var Mockery\MockInterface|WP_Filesystem_Direct
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
		Functions\when('home_url')->justReturn($config['home_url']);
		Functions\when('get_rocket_parse_url')->alias(function ($url) use ($config) {
			if(! key_exists($url, $config['parsed_url'])) {
				return;
			}
			return $config['parsed_url'][$url];
		});

		$this->configureIsDir($config, $expected);
		$this->configureExists($config, $expected);

        $this->assertSame($expected['output'], $this->filesystemcache->deleteMultiple($config['keys']));
    }

	protected function configureIsDir($config, $expected) {
		foreach ($config['is_dir'] as $path => $out) {
			$this->filesystem->shouldReceive('is_dir')->with($path)->andReturn($out);
			if($out)  {
				Functions\expect('rocket_rrmdir')->andReturn($path, [], $this->filesystem);
			} else {
				$this->filesystem->shouldReceive('delete')->with($path)->andReturn(true);
			}
		}
	}

	protected function configureExists($config, $expected) {
		foreach ($config['exists'] as $path => $out) {
			$this->filesystem->shouldReceive('exists')->with($path)->andReturn($out);
		}
	}
}
