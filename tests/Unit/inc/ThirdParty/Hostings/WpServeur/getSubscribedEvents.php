<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WpServeur;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WpServeur;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WpServeur::get_subscribed_events
 *
 * @group WpServeur
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
	public function testShouldReturnExpectedEvents() {
		$this->assertSame(
			[
				'do_rocket_varnish_http_purge'             => 'return_true',
				'rocket_cache_mandatory_cookies'           => [ 'return_empty_array', PHP_INT_MAX ],
				'rocket_varnish_field_settings'            => 'varnish_field',
				'rocket_display_input_varnish_auto_purge'  => 'return_false',
			],
			WpServeur::get_subscribed_events()
		);
	}
}
