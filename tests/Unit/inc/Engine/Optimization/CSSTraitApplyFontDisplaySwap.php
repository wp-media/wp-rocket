<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\CSSTrait::apply_font_display_swap
 *
 * @group  Optimize
 * @group  CSSTrait
 */
class CSSTraitTest extends TestCase {
	use CSSTrait;

	/**
	 * @dataProvider configTestData
	 */
	public function testApplyFontDisplaySwap( $css, $expected ) {
		$this->assertSame(
			$expected,
			$this->apply_font_display_swap( $css )
		);
	}
}
