<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::clean_domain
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_ExcludeDeferJs extends TestCase {
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Avada/excludeDeferJs.php';

	public function testShouldExcludeFromDeferJSMaps() {
		$expected_defer_js = [
			'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
			'maps.googleapis.com',
		];
		$this->assertSame( $expected_defer_js, apply_filters( 'rocket_exclude_defer_js', [] ) );
	}
}
