<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Flywheel::get_subscribed_events
 *
 * @group Flywheel
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
	public function testShouldReturnExpectedEvents() {
		$this->assertSame(
			[
				'rocket_varnish_field_settings'           => 'varnish_field',
				'rocket_display_input_varnish_auto_purge' => 'return_false',
				'do_rocket_varnish_http_purge'             => 'return_true',
				'do_rocket_generate_caching_files'         => 'return_false',
				'rocket_cache_mandatory_cookies'           => [ 'return_empty_array', PHP_INT_MAX ],
				'rocket_varnish_ip'                        => 'varnish_ip',
				'wp_rocket_loaded'                         => 'remove_partial_purge_hooks',
			],
			Flywheel::get_subscribed_events()
		);
	}
}
