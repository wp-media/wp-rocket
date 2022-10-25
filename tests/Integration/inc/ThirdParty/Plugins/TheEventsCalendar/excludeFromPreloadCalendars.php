<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\TheEventsCalendar;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\TheEventsCalendar::exclude_from_preload_calendars
 * @group ThirdParty
 * @group TheEventsCalendar
 */
class Test_ExcludeFromPreloadCalendars extends TestCase
{
	use FilterTrait;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept( 'rocket_preload_exclude_urls', 'exclude_from_preload_calendars', 10 );
	}

	public function tear_down() {
		$this->restoreWpFilter( 'rocket_preload_exclude_urls' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->assertSame($expected, apply_filters('rocket_preload_exclude_urls', $config));
	}
}
