<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\UnlimitedElements;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\UnlimitedElements::is_activated
 *
 * @group UnlimitedElements
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests UnlimitedElements::is_activated() against the presence/absence of UNLIMITED_ELEMENTS_INC.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['unlimited_elements_inc'] ) {
			$this->constants['UNLIMITED_ELEMENTS_INC'] = $config['unlimited_elements_inc'];
		}

		$this->assertSame( $expected, UnlimitedElements::is_activated() );
	}
}
