<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::add_hidden_async_css_mobile
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	use ProviderTrait;
	use IsolateHookTrait;

	protected static $provider_class = 'Settings';

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('rocket_hidden_settings_fields', 'add_hidden_async_css_mobile');
	}

	public function tear_down()
	{
		$this->restoreWpHook('rocket_hidden_settings_fields');
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_hidden_settings_fields', $hidden_fields )
		);
	}
}
