<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

class Test_ReformatShopUrlForPreload extends TestCase
{
    use WooTrait;
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        Functions\when('get_post_type_archive_link')->justReturn($config['post_type_link']);
        $this->assertSame($expected, apply_filters('after_rocket_clean_post_urls', $config['urls'], $config['post']));
    }
}
