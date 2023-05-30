<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::disable_on_official
 */
class Test_disableOnOfficial extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
        do_action('enable_cloudflare/cloudflare.php');
    }
}
