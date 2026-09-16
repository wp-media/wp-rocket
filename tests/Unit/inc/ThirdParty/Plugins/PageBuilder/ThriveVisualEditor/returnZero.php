<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor::return_zero
 *
 * @group ThriveVisualEditor
 * @group ThirdParty
 */
class Test_ReturnZero extends TestCase {
	public function testShouldReturnZeroToForceHumanVisitor() {
		$this->assertSame( 0, ( new ThriveVisualEditor() )->return_zero() );
	}
}
