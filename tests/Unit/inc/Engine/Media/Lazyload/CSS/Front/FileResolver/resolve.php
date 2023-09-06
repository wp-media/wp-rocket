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
		Functions\expect('rocket_url_to_path')->with($expected['stripped_url'], PHP_URL_HOST)->andReturn($config['path']);
        $this->assertSame($expected['output'], $this->fileresolver->resolve($config['url']));
    }
}
