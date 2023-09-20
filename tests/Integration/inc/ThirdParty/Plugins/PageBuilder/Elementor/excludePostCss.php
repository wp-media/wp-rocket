<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_css
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostCss extends TestCase {
	public function tear_down() {
		delete_option( 'elementor_css_print_method' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $print_method, $excluded, $expected ) {
		add_option( 'elementor_css_print_method',  $print_method );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_css', $excluded )
		);
	}
}
