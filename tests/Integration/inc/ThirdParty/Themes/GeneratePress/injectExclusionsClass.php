<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\GeneratePress;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\GeneratePress::inject_exclusions_class
 *
 * @group Themes
 * @group GeneratePress
 */
class Test_InjectExclusionsClass extends TestCase {
	/**
	 * Test that the exclusion class is added to footer classes.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'generate_footer_class', $config['default_class'] )
		);
	}
}
