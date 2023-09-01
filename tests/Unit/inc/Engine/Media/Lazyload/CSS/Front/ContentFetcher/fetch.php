<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\ContentFetcher;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\ContentFetcher;


use WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Front\ContentFetcher::fetch
 */
class Test_fetch extends TestCase {

	/**
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

    /**
     * @var ContentFetcher
     */
    protected $contentfetcher;



    public function set_up() {
        parent::set_up();

		$this->filesystem = \Mockery::mock(WP_Filesystem_Direct::class);

        $this->contentfetcher = new ContentFetcher($this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\expect('wp_http_validate_url')->with($config['path'])->andReturn($config['is_url']);
		$this->configure_filesystem($config);
		$this->configure_url($config);
		$this->assertSame($expected, $this->contentfetcher->fetch($config['path'], $config['destination']));
    }

	protected function configure_filesystem($config) {
		if( $config['is_url']) {
			return;
		}
		$this->filesystem->expects()->get_contents($config['path'])->andReturn($config['content']);
	}

	protected function configure_url($config) {
		if( ! $config['is_url']) {
			return;
		}
		Functions\expect('wp_remote_get')->with($config['path'])->andReturn($config['response']);
		Functions\expect('wp_remote_retrieve_response_code')->with($config['response'])->andReturn($config['code']);
		Functions\expect('wp_remote_retrieve_body')->with($config['response'])->andReturn($config['body']);
	}
}
