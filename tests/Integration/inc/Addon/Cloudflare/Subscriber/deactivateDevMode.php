<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::deactivate_devmode
 *
 * @group Cloudflare
 */
class TestDeactivateDevmode extends TestCase {
	use HttpRequestTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// deactivate_devmode() itself saves the settings option, which would otherwise trigger
		// display_settings_notice()'s real zone lookup as a side effect unrelated to this test.
		$this->unregisterAllCallbacks( 'pre_update_option_wp_rocket_settings' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'pre_update_option_wp_rocket_settings' );

		$this->tear_down_http();

		parent::tear_down();
	}

	public function testShouldDoExpected() {
		do_action( 'rocket_cron_deactivate_cloudflare_devmode' );

		$options = get_option( 'wp_rocket_settings', [] );

		$this->assertSame(
			0,
			$options['cloudflare_devmode']
		);
	}
}
