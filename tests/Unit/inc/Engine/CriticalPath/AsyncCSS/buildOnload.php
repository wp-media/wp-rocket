<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AsyncCSS;

use WP_Rocket\Engine\CriticalPath\AsyncCSS;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AsyncCSS::build_onload
 *
 * @group  CriticalPath
 * @group  AsyncCSS
 * @group  DOM
 */
class Test_BuildOnload extends TestCase {

	public function setUp() {
		parent::setUp();

		$this->default_config['critical_css'] = [ 'get_current_page_critical_css' => 'something' ];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAsyncCss( $html, $expected_onload, $expected_html ) {
		$this->setUpTest( $html );

		$build_onload = $this->get_reflective_method( 'build_onload', AsyncCSS::class );

		// Run it.
		$css_links = $this->dom->query( '//link[@type="text/css"]' );
		foreach ( $css_links as $index => $css ) {
			$build_onload->invoke( $this->instance, $css );
			$this->assertEquals( $expected_onload[ $index ], $css->getAttribute( 'onload' ) );
		}

		$this->assertEquals(
			$this->format_the_html( $expected_html ),
			$this->format_the_html( $this->dom->saveHtml() )
		);
	}
}
