<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\IEConditionalSubscriber;


class Test_InjectIeConditionals extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testInjectIeConditionals($html, $expected, $conditionals)
    {
        $this->setConditionalsValue($conditionals);
        $actual = self::$subscriber->inject_ie_conditionals($html);
        $this->assertEquals($this->format_the_html($expected), $this->format_the_html($actual));
    }
}