<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\WPGeotargeting;

use WP_Rocket\ThirdParty\Plugins\WPGeotargeting;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\WPGeotargeting::maybe_disable_rules
 */
class Test_maybeDisableRules extends TestCase {

    /**
     * @var WPGeotargeting
     */
    protected $wpgeotargeting;

    public function set_up() {
        parent::set_up();

        $this->wpgeotargeting = new WPGeotargeting();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\expect('wp_parse_url')->with($config['current_url'], PHP_URL_QUERY)->andReturn($config['query']);

        $this->assertSame($expected, $this->wpgeotargeting->maybe_disable_rules($config['bool'], $config['opts'], $config['current_url']));
    }
}
