<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\StudioPress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\StudioPress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\StudioPress::get_subscribed_events
 *
 * @group  StudioPress
 * @group  ThirdParty
 */
class Test_GetSubscribedEvents extends TestCase {

	public function testShouldReturnBothHooksUnconditionally() {
		$this->assertSame(
			[
				'admin_init'                => 'clear_cache_after_accelerator',
				'rocket_after_clean_domain' => 'clean_accelerator_cache',
			],
			StudioPress::get_subscribed_events()
		);
	}
}
