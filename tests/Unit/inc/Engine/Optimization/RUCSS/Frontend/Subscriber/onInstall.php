<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::on_install
 */
class Test_onInstall extends TestCase {

    /**
     * @var UsedCSS
     */
    protected $used_css;

	protected $context;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->used_css = Mockery::mock(UsedCSS::class);

		$this->context = Mockery::mock(ContextInterface::class);

        $this->subscriber = new Subscriber($this->used_css, $this->context);
    }

    public function testShouldDoAsExpected()
    {
		Functions\expect('update_option')->with('wp_rocket_no_licence', 0);
        $this->subscriber->on_install([]);
    }
}
