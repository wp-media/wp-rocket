<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\MappingFormatter;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter::format
 */
class Test_format extends TestCase {

    /**
     * @var MappingFormatter
     */
    protected $mappingformatter;

    public function set_up() {
        parent::set_up();

        $this->mappingformatter = new MappingFormatter();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->mappingformatter->format($config['data']));
    }
}
