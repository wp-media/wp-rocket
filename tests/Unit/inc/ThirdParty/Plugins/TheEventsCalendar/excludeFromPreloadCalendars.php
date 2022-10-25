<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\TheEventsCalendar;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;

class Test_ExcludeFromPreloadCalendars extends TestCase
{
	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new TheEventsCalendar();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertSame($expected, $this->subscriber->exclude_from_preload_calendars($config));
	}
}
