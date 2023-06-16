<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;


use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::configurations_changed
 */
class Test_configurationsChanged extends TestCase {

	protected $ajax_handler;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();

		$this->ajax_handler = Mockery::mock(AjaxHandler::class);

        $this->subscriber = new Subscriber($this->ajax_handler);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->returnArg();
		Functions\when('rocket_create_options_hash')->justReturn($config['created_hash']);
		Functions\when('get_option')->alias(function ($name) use ($config) {
			if('WP_ROCKET_SLUG' === $name) {
				return $config['rocket_option'];
			}
			if(Subscriber::LAST_OPTION_HASH === $name) {
				return $config['last_option_hash'];
			}

			return false;
		});
        $this->assertSame($expected, $this->subscriber->configurations_changed());
    }
}
