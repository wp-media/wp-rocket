<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\PreloadFonts\Database\Rows\PreloadFonts;

use WP_Rocket\Engine\Media\PreloadFonts\Database\Rows\PreloadFonts;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreloadFonts\Database\Rows\PreloadFonts::has_preload_fonts
 *
 * @group  Preload
 * @group  PreloadFonts
 */
class Test_HasPreloadFonts extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testHasPreloadFonts($config, $expected) {
		$preloadFonts = new PreloadFonts((object) $config);
		$this->assertSame($expected, $preloadFonts->has_preload_fonts());
	}
}
