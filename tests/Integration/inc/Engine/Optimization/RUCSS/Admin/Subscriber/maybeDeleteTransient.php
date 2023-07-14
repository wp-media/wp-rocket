<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::maybe_delete_transient
 */
class Test_maybeDeleteTransient extends TestCase {

	use FilterTrait;

	public function set_up()
	{
		parent::set_up();
		set_transient('wp_rocket_no_licence', true);
		$this->unregisterAllCallbacksExcept('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ), 'maybe_delete_transient');
	}

	public function tear_down()
	{
		$this->restoreWpFilter('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ));
		delete_transient('wp_rocket_no_licence');
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		apply_filters('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ), $config['old_value'], $config['value']);
		$result = get_transient('wp_rocket_no_licence');
    	$this->assertSame($expected, (bool) $result);
	}
}
