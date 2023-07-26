<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\Extractor;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;


use WP_Rocket\Tests\Unit\TestCase;

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
        $this->assertEqualsCanonicalizing($expected, $this->extractor->extract($config['content']));
    }
}
