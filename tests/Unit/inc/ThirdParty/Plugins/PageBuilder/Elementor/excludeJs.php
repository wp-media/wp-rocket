<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_js
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeJs extends TestCase {
	private $options;
	private $elementor;

	public function setUp(): void {
		parent::setUp();

		$this->options   = Mockery::mock( Options_Data::class );
		$this->elementor = new Elementor( $this->options, null, Mockery::mock( HTML::class ));
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'minify_concatenate_js', false )
			->andReturn( $config['combine_js'] );

		$this->assertSame(
			$expected,
			$this->elementor->exclude_js( [] )
		);
	}
}
