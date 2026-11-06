<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\TheEventsCalendar;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\TheEventsCalendar::exclude_from_preload_calendars
 * @group ThirdParty
 * @group TheEventsCalendar
 */
class Test_ExcludeFromPreloadCalendars extends TestCase
{
	use IsolateHookTrait;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept( 'rocket_preload_exclude_urls', 'exclude_from_preload_calendars', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_preload_exclude_urls' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		if($config['exists']) {
			Functions\expect('tribe_get_option')->andReturn($config['slug']);
		}
		$this->assertSame($expected, apply_filters('rocket_preload_exclude_urls', $config['params']));
	}
}
