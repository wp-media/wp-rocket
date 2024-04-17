<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

class Test_IsRocketGenerateCachingMobileFiles extends TestCase
{
    protected static $use_settings_trait = true;
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpectedOptionValue(array $settings, $expected)
    {
        $this->mergeExistingSettingsAndUpdate($settings);
        $this->assertSame($expected, is_rocket_generate_caching_mobile_files());
    }
}
