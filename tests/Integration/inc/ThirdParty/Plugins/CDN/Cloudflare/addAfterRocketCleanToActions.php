<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_after_rocket_clean_to_actions
 */
class Test_addAfterRocketCleanToActions extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, apply_filters('cloudflare_purge_url_actions', $config['enable']));
    }
}
