<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::treeshake
 */
class Test_treeshake extends TestCase {

    /**
     * @var UsedCSS
     */
    protected $used_css;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->used_css = Mockery::mock(UsedCSS::class);

        $this->subscriber = new Subscriber($this->used_css);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->treeshake($config['html']));

    }
}
