<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Ajax\AjaxHandler;

use WP_Rocket\Engine\Common\Ajax\AjaxHandler;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Common\Ajax\AjaxHandler::redirect
 */
class Test_redirect extends TestCase {

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
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->justReturn(true);

		if('' === $config['url']) {
			Functions\expect('wp_get_referer')->andReturn($config['referer']);
		}
		Functions\expect('wp_safe_redirect')->andReturn($expected);
		Functions\expect('wp_die');
        $this->ajaxhandler->redirect($config['url']);
    }
}
