<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_css
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostCss extends TestCase {
	private $elementor;

	public function setUp(): void {
		parent::setUp();

		$this->elementor = new Elementor( Mockery::mock( Options_Data::class ), null, Mockery::mock( HTML::class ) );

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
