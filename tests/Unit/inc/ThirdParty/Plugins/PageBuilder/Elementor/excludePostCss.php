<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_css
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostCss extends TestCase {
	use ElementorTestTrait;


	public function setUp(): void {
		parent::setUp();
		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $print_method, $excluded, $expected ) {
		Functions\expect( 'get_option' )
			->once()
			->with( 'elementor_css_print_method' )
			->andReturn( $print_method );

		Functions\when( 'wp_get_upload_dir' )->justReturn(
			[
				'baseurl' => 'http://example.org/wp-content/uploads',
			]
		);

		$this->assertSame(
			$expected,
			$this->elementor->exclude_post_css( $excluded )
		);
	}
}
