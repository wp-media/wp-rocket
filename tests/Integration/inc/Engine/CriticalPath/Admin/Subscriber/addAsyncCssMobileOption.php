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
	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldAddOption( $options, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_first_install_options', $options )
		);
	}

	public function dataProvider() {
		$dir  = WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CriticalPath/Admin/Settings/';
		$data = $this->getTestData( $dir, str_replace( '.php', '', basename( __FILE__ ) ) );

		return isset( $data['test_data'] )
			? $data['test_data']
			: $data;
	}
}
