<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::add_async_css_mobile_option
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_AddAsyncCssMobileOption extends TestCase {
	use ProviderTrait;

	protected static $class_name = 'Settings';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_first_install_options', $options )
		);
	}
}
