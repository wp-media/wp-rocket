<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AsyncCSS;

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
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AsyncCSS/modifyHtml.php';

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAsyncCss( $html, $expected, $config = [] ) {
		$this->setUpTest( $html, $config, ! is_null( $expected ) );

		if ( is_null( $expected ) ) {
			$this->assertNull( $this->instance );

			return;
		}

		add_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );

		$this->assertInstanceOf( AsyncCSS::class, $this->instance );
		$actual = $this->instance->modify_html( $html );

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}

	public function rocket_exclude_async_css( $value ) {
		if ( ! isset( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}

		if ( empty( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}


		return $this->test_config['critical_css']['get_exclude_async_css'];
	}
}
