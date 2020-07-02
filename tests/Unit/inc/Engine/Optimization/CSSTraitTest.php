<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization;

use PHPUnit\Framework\TestCase;

//use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;
use WP_Rocket\Engine\Optimization\CSSTrait;

/**
 * Tests for CSSTrait
 *
 * @since 3.7
 */
class CSSTraitTest extends TestCase {
	use CSSTrait;

	public function testApplyFontDisplaySwap() {
		$css_content = <<<CSS
@font-face {
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
@font-face {
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-display: auto;
	font-style: normal;
}
CSS;

		$expected = <<<CSS
@font-face{font-display:swap;
  font-family: 'MyWebFont'; /* Define the custom font name */
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff'); /* Define where the font can be downloaded */
        /* Define how the browser behaves during download */
}
@font-face{
	font-family: 'ETmodules';
	src: url("core/admin/fonts/modules.eot");
	src: url("core/admin/fonts/modules.eot#iefix")
	format("woff"), url("core/admin/fonts/modules.svg#ETModules");
	font-weight: normal;
	font-display: auto;
	font-style: normal;
}
CSS;


		$this->assertEquals( $expected, $this->apply_font_display_swap( $css_content ) );
	}
}
