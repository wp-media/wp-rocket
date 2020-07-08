<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

use WP_Rocket\Engine\Optimization\CSSTrait;

/**
 * @covers \WP_Rocket\Engine\Optimization\CSSTrait::apply_font_display_swap
 *
 * @group  Optimize
 */
class CSSTraitTest extends TestCase {
	use CSSTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/CSSTraitApplyFontDisplaySwap.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testApplyFontDisplaySwap( $css, $expected ) {
		$this->assertEquals( $expected, $this->apply_font_display_swap( $css ) );
	}
}
