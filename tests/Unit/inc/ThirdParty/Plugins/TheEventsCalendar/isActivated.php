<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\TheEventsCalendar;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\TheEventsCalendar::is_activated
 *
 * @group TheEventsCalendar
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests TheEventsCalendar::is_activated() against the presence/absence of TRIBE_EVENTS_FILE.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['tribe_events_file'] ) {
			$this->constants['TRIBE_EVENTS_FILE'] = $config['tribe_events_file'];
		}

		$this->assertSame( $expected, TheEventsCalendar::is_activated() );
	}
}
