<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddFootprint extends TestCase
{
    /**
     * @dataProvider providerTestData
     */
    public function testShouldAddFootprint($white_label_footprint, $html, $expected)
    {
        if ($white_label_footprint) {
            $this->white_label_footprint = $white_label_footprint;
        }
        $this->assertSame($expected, apply_filters('rocket_buffer', $html));
    }
    public function providerTestData()
    {
        return $this->getTestData(__DIR__ . '/Int', 'addFootprint');
    }
}
