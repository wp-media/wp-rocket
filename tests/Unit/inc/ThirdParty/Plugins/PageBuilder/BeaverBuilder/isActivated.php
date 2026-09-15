<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::is_activated
 *
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests BeaverBuilder::is_activated() against the presence/absence of FL_BUILDER_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['fl_builder_version'] ) {
			$this->constants['FL_BUILDER_VERSION'] = $config['fl_builder_version'];
		}

		$this->assertSame( $expected, BeaverBuilder::is_activated() );
	}
}
