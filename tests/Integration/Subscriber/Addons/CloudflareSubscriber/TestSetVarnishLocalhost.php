<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Addons\CloudflareSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::set_varnish_localhost
 * @group Cloudflare
 */
class Test_SetVarnishLocalhost extends TestCase {
	/**
	 * Test should return unchanged array when Cloudflare is disabled
	 */
	public function testShouldReturnDefaultWhenCloudflareDisabled() {
		add_filter( 'pre_get_rocket_option_do_cloudflare', '__return_false');

		$this->assertSame(
			[],
			apply_filters('rocket_varnish_ip', [] )
		);

		remove_filter( 'pre_get_rocket_option_do_cloudflare', '__return_false');
	}

	/**
	 * Test should return unchanged array when Varnish is disabled
	 */
	public function testShouldReturnDefaultWhenVarnishDisabled() {
		add_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		add_filter( 'pre_get_rocket_option_varnish_auto_purge', '__return_false');

		$this->assertSame(
			[],
			apply_filters('rocket_varnish_ip', [] )
		);

		remove_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', '__return_false');
	}

	/**
	 * Test should update the array when Varnish & Cloudflare are enabled
	 */
	public function testShouldReturnLocalhostWhenVarnishEnabled() {
		add_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		add_filter( 'pre_get_rocket_option_varnish_auto_purge', '__return_true');

		$this->assertSame(
			[ 'localhost' ],
			apply_filters('rocket_varnish_ip', [] )
		);

		remove_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		remove_filter( 'pre_get_rocket_option_varnish_auto_purge', '__return_true');
	}

	/**
	 * Test should update the array when Varnish is enabled via filter
	 */
	public function testShouldReturnLocalhostWhenFilterTrue() {
		add_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

		$this->assertSame(
			[ 'localhost' ],
			apply_filters('rocket_varnish_ip', [] )
		);

		remove_filter( 'pre_get_rocket_option_do_cloudflare', '__return_true');
		remove_filter( 'do_rocket_varnish_http_purge', '__return_true' );
	}
}