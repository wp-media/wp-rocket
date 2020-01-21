<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Addons\CloudflareSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

/**
 * @covers WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::get_subscribed_events
 *
 * @group  Cloudflare
 */
class Test_GetSubscribedEvents extends TestCase {

	/**
	 * Setup constants required by Cloudflare
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_SLUG' ) ) {
			define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		}
	}

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'rocket_varnish_ip'                         => 'set_varnish_localhost',
			'rocket_varnish_purge_request_host'         => 'set_varnish_purge_request_host',
			'rocket_cron_deactivate_cloudflare_devmode' => 'deactivate_devmode',
			'after_rocket_clean_domain'                 => 'auto_purge',
			'after_rocket_clean_post'                   => [ 'auto_purge_by_url', 10, 3 ],
			'admin_post_rocket_purge_cloudflare'        => 'purge_cache',
			'init'                                      => [ 'set_real_ip', 1 ],
			'update_option_' . WP_ROCKET_SLUG           => [ 'save_cloudflare_options', 10, 2 ],
			'pre_update_option_' . WP_ROCKET_SLUG       => [ 'save_cloudflare_old_settings', 10, 2 ],
			'admin_notices'                             => [
				[ 'maybe_display_purge_notice' ],
				[ 'maybe_print_update_settings_notice' ],
			],
		];

		$this->assertSame(
			$events,
			CloudflareSubscriber::get_subscribed_events()
		);
	}
}
