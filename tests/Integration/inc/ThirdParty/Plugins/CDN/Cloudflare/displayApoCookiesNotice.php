<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cookies_notice
 *
 * @group AdminOnly
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_displayApoCookiesNotice extends AdminTestCase {
	use HttpRequestTrait;

	private static $admin_user_id = 0;
	private static $contributer_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'rocket_manage_options' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public function set_up()
	{
		parent::set_up();

		$this->setup_http();

		add_filter('pre_option_automatic_platform_optimization', [$this, 'automatic_platform_optimization']);
		add_filter('rocket_cache_mandatory_cookies', [$this, 'mandatory_cookies']);
		add_filter('rocket_cache_dynamic_cookies', [$this, 'dynamic_cookies']);
		add_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		add_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		add_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		add_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );
	}

	public function tear_down()
	{
		remove_filter('pre_option_automatic_platform_optimization', [$this, 'automatic_platform_optimization']);
		remove_filter('rocket_cache_mandatory_cookies', [$this, 'dynamic_cookies']);
		remove_filter('rocket_cache_dynamic_cookies', [$this, 'mandatory_cookies']);
		remove_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		remove_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		remove_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		remove_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);
		$this->restoreWpHook( 'current_screen' );

		// admin_notices fires every registered subscriber, not just Cloudflare's; ModPagespeed's
		// detection ping (also mocked below) caches its result for a day, so clear that cache
		// rather than leak a stale value into whichever test runs it next in this process.
		delete_transient( 'rocket_mod_pagespeed_enabled' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->config = $config;

		// admin_notices fires every registered subscriber, not just Cloudflare's; these URLs
		// belong to RocketCDN's subscription check (a non-404 status avoids its website-search
		// fallback call) and ModPagespeed's detection ping, both of which run unconditionally on
		// that hook for an admin on the wprocket settings screen.
		$this->config['http'] = [
			'https://rocketcdn.me/api/subscription/example.org/status' => [
				'headers'  => [],
				'body'     => wp_json_encode( [ 'success' => false ] ),
				'response' => [ 'code' => 200 ],
				'cookies'  => [],
			],
			'http://example.org' => [
				'headers'  => [],
				'body'     => '',
				'response' => [ 'code' => 200 ],
				'cookies'  => [],
			],
		];

		set_current_screen( $config['screen']->id );

		if ( $config['can'] ) {
			$user_id = self::$admin_user_id;
		}else{
			$user_id = self::$contributer_user_id;
		}
		wp_set_current_user( $user_id );

		ob_start();
		do_action('admin_notices');
		$notices = ob_get_clean();
		if($config['should_display']) {
			$this->assertStringContainsString(
				$this->format_the_html( $expected['notice_content'] ),
				$this->format_the_html( $notices )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $expected['notice_content'] ),
				$this->format_the_html( $notices )
			);
		}
	}

	public function automatic_platform_optimization() {
		return $this->config['automatic_platform_optimization'];
	}

	public function mandatory_cookies() {
		return $this->config['mandatory_cookies'];
	}

	public function dynamic_cookies() {
		return $this->config['dynamic_cookies'];
	}

	public function active_plugins() {
		return $this->config['active_plugins'];
	}

	public function cloudflare_cached_domain_name() {
		return $this->config['cloudflare_cached_domain_name'];
	}

	public function cloudflare_api_key() {
		return $this->config['cloudflare_api_key'];
	}

	public function cloudflare_api_email() {
		return $this->config['cloudflare_api_email'];
	}
}
