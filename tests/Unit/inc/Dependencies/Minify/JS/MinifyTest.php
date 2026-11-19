<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Dependencies\Minify\JS;

use WP_Rocket\Dependencies\Minify\JS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Dependencies\Minify\JS::minify
 *
 * @group Minify
 * @group MinifyJS
 */
class MinifyTest extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldNotAlterTemplateLiterals( $input, $expected ) {
		$this->assertSame( $expected, ( new JS( $input ) )->minify() );
	}
}
