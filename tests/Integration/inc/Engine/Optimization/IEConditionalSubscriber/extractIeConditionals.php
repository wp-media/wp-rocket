<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\IEConditionalSubscriber;


class Test_ExtractIeConditionals extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testExtractIeConditionals($html, $expected, $conditionals)
    {
        $actual = self::$subscriber->extract_ie_conditionals($html);
        $this->assertEquals($this->format_the_html($expected), $this->format_the_html($actual));
        $this->assertSame($conditionals, $this->getConditionalsValue());
    }
}
