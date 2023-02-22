<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Ngnix;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Ngnix::better_nginx_compatibility
 * @group Nginx
 */
class Test_betterNginxCompatibility extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, apply_filters('rocket_cache_query_strings', $config));
    }
}
