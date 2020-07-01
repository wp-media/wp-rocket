<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AsyncCSS;

use WP_Rocket\Engine\CriticalPath\AsyncCSS;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AsyncCSS::modify_html
 * @uses   \WP_Rocket\Engine\DOM\HTMLDocument::query
 * @uses   \WP_Rocket\Engine\DOM\HTMLDocument::get_html
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_current_page_critical_css
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_exclude_async_css
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @uses   ::rocket_get_constant
 * @uses   ::is_rocket_post_excluded_option
 *
 * @group  CriticalPath
 * @group  AsyncCSS
 * @group  DOM
 * @group  abc
 */
class Test_ModifyHtml extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAsyncCss( $html, $expected, $config = [] ) {
		$this->setUpTest( $html, $config );

		if ( is_null( $expected ) ) {
			$this->assertNull( $this->instance );

			return;
		}

		$this->assertInstanceOf( AsyncCSS::class, $this->instance );
		$actual = $this->instance->modify_html( $html );

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}
}
