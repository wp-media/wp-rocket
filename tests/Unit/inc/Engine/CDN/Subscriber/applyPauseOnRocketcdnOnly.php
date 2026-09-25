<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the removal of \WP_Rocket\Engine\CDN\Subscriber::apply_pause_on_rocketcdn_only
 *
 * Regression coverage for the Task 9.1 root-cause fix (issue #8707): the
 * `get_rocket_option_cdn` filter used to force-report `cdn` as enabled on the
 * front end whenever `cdn_type === 'byocdn'`, regardless of the actual `cdn`
 * value — silently ignoring a BYOCDN user's explicit "off" state. The method
 * and its hook registration were removed entirely rather than patched, since
 * `Rest::apply_cdn_mode()` now keeps `cdn`/`cdn_type` in sync on every
 * toggle-driven transition, making the override both unnecessary and actively
 * wrong for the remaining flows that write `cdn` directly.
 *
 * @group CDN
 */
class Test_ApplyPauseOnRocketcdnOnly extends TestCase {

	public function testShouldNotDeclareTheRemovedMethod(): void {
		$this->assertFalse(
			method_exists( Subscriber::class, 'apply_pause_on_rocketcdn_only' ),
			'apply_pause_on_rocketcdn_only() must be fully removed — a pass-through filter left in its place would be dead weight and risks a subtle type-cast regression for other get_rocket_option_cdn consumers.'
		);
	}

	public function testShouldNoLongerSubscribeToGetRocketOptionCdn(): void {
		$events = Subscriber::get_subscribed_events();

		$this->assertArrayNotHasKey(
			'get_rocket_option_cdn',
			$events,
			'The get_rocket_option_cdn filter hook must be removed alongside the method it registered — BYOCDN must rely on the raw, unfiltered cdn option value.'
		);
	}
}
