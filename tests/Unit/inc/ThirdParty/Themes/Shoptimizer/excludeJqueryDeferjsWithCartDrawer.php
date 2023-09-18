<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Shoptimizer;

use WP_Rocket\ThirdParty\Themes\Shoptimizer;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Shoptimizer::exclude_jquery_deferjs_with_cart_drawer
 */
class Test_excludeJqueryDeferjsWithCartDrawer extends TestCase {

    /**
     * @var Shoptimizer
     */
    protected $shoptimizer;

    public function set_up() {
        parent::set_up();

        $this->shoptimizer = new Shoptimizer();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('shoptimizer_get_option')->justReturn($config['option']);
        $this->assertSame($expected, $this->shoptimizer->exclude_jquery_deferjs_with_cart_drawer($config['exclusions']));
    }
}
