<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Front\RuleFormatter;

use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter::format
 */
class Test_format extends TestCase {

    /**
     * @var RuleFormatter
     */
    protected $ruleformatter;

    public function set_up() {
        parent::set_up();

        $this->ruleformatter = new RuleFormatter();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->ruleformatter->format($config['css'], $config['data']));
    }
}
