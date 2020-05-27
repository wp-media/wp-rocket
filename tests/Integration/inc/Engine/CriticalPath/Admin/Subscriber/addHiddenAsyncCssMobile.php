<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::add_hidden_async_css_mobile
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_hidden_settings_fields', $hidden_fields )
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
