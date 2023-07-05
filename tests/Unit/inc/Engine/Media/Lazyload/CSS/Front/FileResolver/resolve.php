<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\FileResolver;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver::resolve
 */
class Test_resolve extends TestCase {

    /**
     * @var FileResolver
     */
    protected $fileresolver;

    public function set_up() {
        parent::set_up();

        $this->fileresolver = new FileResolver();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('home_url')->justReturn($config['home_url']);
		Functions\when('get_home_path')->justReturn($config['home_path']);
		Functions\expect('wp_parse_url')->with($expected['url'])->andReturn($config['parsed_url']);
		Functions\expect('wp_parse_url')->with($expected['home_url'], PHP_URL_HOST)->andReturn($config['host_url']);
        $this->assertSame($expected['output'], $this->fileresolver->resolve($config['url']));
    }
}
