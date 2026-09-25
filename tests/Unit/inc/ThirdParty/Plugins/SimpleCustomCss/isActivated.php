<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::is_activated
 *
 * @group SimpleCustomCss
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests SimpleCustomCss::is_activated() against the presence/absence of SCCSS_FILE.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['sccss_file'] ) {
			$this->constants['SCCSS_FILE'] = $config['sccss_file'];
		}

		$this->assertSame( $expected, SimpleCustomCss::is_activated() );
	}
}
