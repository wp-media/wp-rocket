<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::save_hash_on_update_options
 * @group AdminOnly
 */
class Test_saveHashOnUpdateOptions extends TestCase {

	use FilterTrait;

	public function set_up()
	{
		parent::set_up();
		delete_option( Subscriber::LAST_OPTION_HASH );
		$this->unregisterAllCallbacksExcept('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), 'save_hash_on_update_options');
	}

	public function tear_down()
	{
		$this->restoreWpFilter('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ));
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->assertSame($expected['value'], apply_filters('update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ), $config['oldvalue'], $config['value']));
		$this->assertSame($expected['hash'], get_option(Subscriber::LAST_OPTION_HASH));
    }
}
