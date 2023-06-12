<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::update_addon_field
 */
class Test_updateAddonField extends TestCase {

	protected $config;

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		add_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		add_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		add_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);
	}

	public function tear_down()
	{
		remove_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		remove_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		remove_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		remove_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);
		parent::tear_down();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->config = $config;
        $this->assertSame($expected, apply_filters('rocket_cloudflare_field_settings', $config['settings']));
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
