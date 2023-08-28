<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\Extractor;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor::extract
 */
class Test_extract extends TestCase {

    /**
     * @var Extractor
     */
    protected $extractor;

    public function set_up() {
        parent::set_up();

        $this->extractor = new Extractor();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('wp_parse_url')->justReturn('example.org');
		Functions\when('home_url')->justReturn('http://example.org');
        $this->assertEqualsCanonicalizing($expected, $this->extractor->extract($config['content']));
    }
}
