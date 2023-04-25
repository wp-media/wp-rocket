<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::save_hash_on_update_options
 */
class Test_saveHashOnUpdateOptions extends TestCase {

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
		Functions\when('rocket_create_options_hash')->justReturn($config['created_hash']);
		if($config['value']) {
			Functions\expect('update_option')->with(Subscriber::LAST_OPTION_HASH, $expected['hash']);
		}
        $this->assertSame($expected['value'], $this->subscriber->save_hash_on_update_options($config['oldvalue'], $config['value']));

    }
}
