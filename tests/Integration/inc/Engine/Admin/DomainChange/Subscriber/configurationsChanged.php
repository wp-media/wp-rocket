<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::configurations_changed
 * @group AdminOnly
 */
class Test_configurationsChanged extends TestCase {

	protected $config;

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_option_' . Subscriber::LAST_OPTION_HASH, [$this, 'get_option_last_option_hash']);
		add_filter('pre_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), [$this, 'get_option_rocket']);
	}

	public function tear_down()
	{
		remove_filter('pre_option_' . Subscriber::LAST_OPTION_HASH, [$this, 'get_option_last_option_hash']);
		remove_filter('pre_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), [$this, 'get_option_rocket']);
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->config = $config;
        $this->assertSame($expected, apply_filters('rocket_configurations_changed', false));
    }

	public function get_option_rocket() {
		return $this->config['rocket_option'];
	}

	public function get_option_last_option_hash() {
		return $this->config['last_option_hash'];
	}
}
