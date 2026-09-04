<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::update_addon_field
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_updateAddonField extends TestCase {

	protected $config;

	/**
	 * @var \WP_Rocket\Event_Management\Event_Manager
	 */
	private $event_manager;

	/**
	 * @var Cloudflare
	 */
	private $cloudflare;

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		add_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		add_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		add_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);

		// PluginResolver gates cloudflare_plugin_subscriber out of the container at boot
		// (the official Cloudflare plugin isn't installed in this test environment), so
		// its rocket_cloudflare_field_settings callback was never wired to the event
		// manager. Build the subscriber directly (same approach Test_ExcludeDelayJs uses
		// for Termly) and wire it here so the filter under test actually fires.
		$container            = apply_filters( 'rocket_container', null );
		$this->cloudflare     = new Cloudflare(
			$container->get( 'options' ),
			$container->get( 'options_api' ),
			$container->get( 'beacon' ),
			$container->get( 'cloudflare_plugin_facade' )
		);
		$this->event_manager = $container->get( 'event_manager' );
		$this->event_manager->add_subscriber( $this->cloudflare );
	}

	public function tear_down()
	{
		$this->event_manager->remove_subscriber( $this->cloudflare );

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
