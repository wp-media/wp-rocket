<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\RevolutionSlider;

use WP_Rocket\Tests\Integration\TestCase;
/**
 * @covers \WP_Rocket\ThirdParty\Plugins\RevolutionSlider::exclude_defer_js
 *
 * @group  RevolutionSlider
 * @group  ThirdParty
 */
class Test_ExcludeDeferJs extends TestCase {

	public function testShouldExcludeFromDeferJSJQuery() {
		$expected_defer_js = [
			'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
		    '/jquery-migrate(.min)?.js',
		];
		$this->assertSame( $expected_defer_js, apply_filters( 'rocket_exclude_defer_js', [] ) );
	}
}
