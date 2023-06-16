<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Ajax\AjaxHandler;

use CoquardcyrWpArticleScheduler\Dependencies\League\Plates\Template\Func;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Common\Ajax\AjaxHandler::validate_referer
 */
class Test_validateReferer extends TestCase {

    /**
     * @var AjaxHandler
     */
    protected $ajaxhandler;

    public function set_up() {
        parent::set_up();

        $this->ajaxhandler = new AjaxHandler();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		if(key_exists('referer', $config['args'])) {
			Functions\expect('check_admin_referer')->with($expected['referer']);
		}

		if(key_exists('capacities', $config['args'])) {
			Functions\expect('current_user_can')->with($expected['capacity'])->andReturn($config['user_can']);
		}

        $this->assertSame($expected['result'], $this->ajaxhandler->validate_referer($config['args']));
    }
}
