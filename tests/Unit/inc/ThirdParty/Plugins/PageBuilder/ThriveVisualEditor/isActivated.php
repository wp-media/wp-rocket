<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\ThriveVisualEditor::is_activated
 *
 * eval() declares tve_editor_url() in the global namespace for the present case;
 * @runInSeparateProcess keeps it isolated. Brain\Monkey's Functions\when() is not used
 * here because it leaves a real global function declared that leaks into the absent set.
 *
 * @group ThriveVisualEditor
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the tve_editor_url() function.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_tve'] ) {
			eval( 'function tve_editor_url() {}' );
		}

		$this->assertSame( $expected, ThriveVisualEditor::is_activated() );
	}
}
