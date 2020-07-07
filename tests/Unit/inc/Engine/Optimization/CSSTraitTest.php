<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

//use PHPUnit\Framework\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;
use WP_Rocket\Engine\Optimization\CSSTrait;

/**
 * Tests for CSSTrait
 *
 * @since 3.7
 */
class CSSTraitTest extends TestCase {
	use CSSTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/CSSTrait.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testApplyFontDisplaySwap( $css, $expected ) {
		$this->assertEquals( $expected, $this->apply_font_display_swap( $css ) );
	}
}
