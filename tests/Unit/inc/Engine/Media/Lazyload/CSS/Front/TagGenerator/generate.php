<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\TagGenerator;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator::generate
 */
class Test_generate extends TestCase {

    /**
     * @var TagGenerator
     */
    protected $taggenerator;

    public function set_up() {
        parent::set_up();

        $this->taggenerator = new TagGenerator();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->taggenerator->generate($config['mapping'], $config['loaded']));

    }
}
