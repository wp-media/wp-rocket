<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Engine\Admin\DomainChange\Subscriber;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::configurations_changed
 */
class Test_configurationsChanged extends TestCase {

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();

        $this->subscriber = new Subscriber();
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
