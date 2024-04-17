<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

class Test_AddAsyncCssMobileOption extends TestCase
{
    use ProviderTrait;
    protected static $provider_class = 'Settings';
    /**
     * @dataProvider providerTestData
     */
    public function testShouldAddOption($options, $expected)
    {
        $filtered_options = apply_filters('rocket_first_install_options', $options);
        $this->assertArrayHasKey('async_css_mobile', $filtered_options);
        $this->assertSame($expected['async_css_mobile'], $filtered_options['async_css_mobile']);
    }
}
