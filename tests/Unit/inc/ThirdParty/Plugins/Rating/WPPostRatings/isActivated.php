<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Rating\WPPostRatings;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Rating\WPPostRatings;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Rating\WPPostRatings::is_activated
 *
 * WP_POSTRATINGS_VERSION is mocked through the rocket_has_constant() stub
 * ($this->constants), so no process isolation is required.
 *
 * @group WPPostRatings
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the WP_POSTRATINGS_VERSION constant.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['wp_postratings_version'] ) {
			$this->constants['WP_POSTRATINGS_VERSION'] = $config['wp_postratings_version'];
		}

		$this->assertSame( $expected, WPPostRatings::is_activated() );
	}
}
