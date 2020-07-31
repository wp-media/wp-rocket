<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes;

/**
 * Tests for WPThemeTestcase
 */
class WPThemeTestcaseTest extends \WP_Rocket\Tests\Integration\WPThemeTestcase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/WPThemeTestcaseTest.php';

	public function testWPThemeTestcase() {

		$this->set_theme( 'divi', 'Divi' )
			->set_child_theme( 'divi-child', 'Divi Child', 'divi' );

		$this->assertFalse( $this->theme->errors() );
		$this->assertFalse( $this->child_theme->errors() );

		$this->assertEquals( 'Divi', $this->theme->get( 'Name' ) );
		$this->assertEquals( 'Divi Child', $this->child_theme->get( 'Name' ) );

		$this->assertEquals( 'divi', $this->theme->get_template() );
		$this->assertEquals( 'divi', $this->child_theme->get_template() );
	}
}
