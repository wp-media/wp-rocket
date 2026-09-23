<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\Cloudflare\Subscriber::add_cdn_helper_message
 * @group Cloudflare
 */
class Test_addCdnHelperMessage extends TestCase {
    // Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
    // Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
    protected static $use_settings_trait = false;

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, apply_filters('rocket_cdn_helper_addons', $config['addons']));
    }
}
