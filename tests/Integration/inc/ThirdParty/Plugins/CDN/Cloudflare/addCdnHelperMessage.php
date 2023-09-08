<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_cdn_helper_message
 */
class Test_addCdnHelperMessage extends TestCase {

	protected $config;

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_active_plugins', [ $this, 'plugin_enabled' ] );
		add_filter( 'pre_option_cloudflare_api_email', [ $this, 'cloudflare_api_email' ] );
		add_filter( 'pre_option_cloudflare_api_key', [ $this, 'cloudflare_api_key' ] );
		add_filter( 'pre_option_cloudflare_cached_domain_name', [ $this, 'cloudflare_cached_domain_name' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_option_active_plugins', [ $this, 'plugin_enabled' ] );
		remove_filter( 'pre_option_cloudflare_api_email', [ $this, 'cloudflare_api_email' ] );
		remove_filter( 'pre_option_cloudflare_api_key', [ $this, 'cloudflare_api_key' ] );
		remove_filter( 'pre_option_cloudflare_cached_domain_name', [ $this, 'cloudflare_cached_domain_name' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_cdn_helper_addons', $config['addons'] )
		);
	}

	public function plugin_enabled( $plugins ) {
		if ( ! $this->config['plugin_enabled'] ) {
			return $plugins;
		}

		if ( ! is_array( $plugins ) ) {
			$plugins = (array) $plugins;
		}

		$plugins[] = 'cloudflare/cloudflare.php';

		return $plugins;
	}

	public function cloudflare_api_email() {
		return $this->config['cf_email'];
	}

	public function cloudflare_api_key() {
		return $this->config['cf_key'];
	}

	public function cloudflare_cached_domain_name() {
		return $this->config['cf_domain'];
	}
}
