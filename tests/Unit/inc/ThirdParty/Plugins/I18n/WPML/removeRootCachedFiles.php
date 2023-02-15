<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\WPML;

use Mockery;
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\I18n\WPML;
use Brain\Monkey\Functions;
class Test_RemoveRootCachedFiles extends TestCase
{
	protected $subscriber;
	protected $filesystem;

	protected function set_up()
	{
		parent::set_up();
		$this->filesystem = Mockery::mock( WP_Filesystem_Direct::class );
		$this->subscriber = new WPML($this->filesystem);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAsExpected( $config ) {
		Functions\expect('home_url')->andReturn($config['home_url']);
		Functions\expect('wp_parse_url')->with($config['home_url'], PHP_URL_HOST)->andReturn($config['host']);
		Functions\expect('_rocket_get_wp_rocket_cache_path')->with($config['home_url'], PHP_URL_HOST)->andReturn($config['path']);
		$this->filesystem->expects()->dirlist($config['cache_path'])->andReturn($config['entries']);
		$this->filesystem->expects()->is_dir($config['entry_path'])->andReturn(false);
		$this->filesystem->expects()->delete($config['entry_path']);
		$this->subscriber->remove_root_cached_files();
	}
}
