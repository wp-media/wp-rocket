<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Nginx;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Nginx;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Nginx::get_subscribed_events
 *
 * @group Nginx
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
	public function testShouldReturnExpectedEvents() {
		$this->assertSame(
			[
				'rocket_cache_query_strings' => 'better_nginx_compatibility',
			],
			Nginx::get_subscribed_events()
		);
	}
}
