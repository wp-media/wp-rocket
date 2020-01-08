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
		update_option(
			'wp_rocket_settings',
			[
				'do_cloudflare' => 0,
			]
		);
		$cloudflare_facade = new CloudflareFacade();
		$cf_subscriber     = new CloudflareSubscriber( new Cloudflare( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), $cloudflare_facade ), new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), new Options() );

		$this->assertSame(
			[],
			$cf_subscriber->set_varnish_localhost( [] )
		);
	}

	/**
	 * Test should return unchanged array when Varnish is disabled
	 */
	public function testShouldReturnDefaultWhenVarnishDisabled() {
		update_option(
			'wp_rocket_settings',
			[
				'do_cloudflare' => 1,
				'varnish_auto_purge' => 0,
			]
		);

		$cloudflare_facade = new CloudflareFacade();
		$cf_subscriber     = new CloudflareSubscriber( new Cloudflare( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), $cloudflare_facade ), new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), new Options() );

		$this->assertSame(
			[],
			$cf_subscriber->set_varnish_localhost( [] )
		);
	}

	/**
	 * Test should update the array when Varnish & Cloudflare are enabled
	 */
	public function testShouldReturnLocalhostWhenVarnishEnabled() {
		update_option(
			'wp_rocket_settings',
			[
				'do_cloudflare' => 1,
				'varnish_auto_purge' => 1,
			]
		);

		$cloudflare_facade = new CloudflareFacade();
		$cf_subscriber     = new CloudflareSubscriber( new Cloudflare( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), $cloudflare_facade ), new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), new Options() );

		$this->assertSame(
			[ 'localhost' ],
			$cf_subscriber->set_varnish_localhost( [] )
		);
	}

	/**
	 * Test should update the array when Varnish is enabled via filter
	 */
	public function testShouldReturnLocalhostWhenFilterTrue() {
		update_option(
			'wp_rocket_settings',
			[
				'do_cloudflare' => 1,
				'varnish_auto_purge' => 0,
			]
		);

		add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

		$cloudflare_facade = new CloudflareFacade();
		$cf_subscriber     = new CloudflareSubscriber( new Cloudflare( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), $cloudflare_facade ), new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), new Options() );

		$this->assertSame(
			[ 'localhost' ],
			$cf_subscriber->set_varnish_localhost( [] )
		);

		remove_filter( 'do_rocket_varnish_http_purge', '__return_true' );
	}
}