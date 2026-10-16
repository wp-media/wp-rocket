<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pagely;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pagely;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pagely::get_subscribed_events
 *
 * @group Pagely
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
	public function testShouldReturnExpectedEvents() {
		$this->assertSame(
			[
				'rocket_after_clean_domain' => 'clean_pagely',
			],
			Pagely::get_subscribed_events()
		);
	}
}
