<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cache_notice
 *
 * @group AdminOnly
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_displayApoCacheNotice extends AdminTestCase {

	private static $admin_user_id = 0;
	private static $contributer_user_id = 0;

	protected $config;

	/**
	 * @var \WP_Rocket\Event_Management\Event_Manager
	 */
	private $event_manager;

	/**
	 * @var Cloudflare
	 */
	private $cloudflare;

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
		add_filter('pre_option_automatic_platform_optimization', [$this, 'automatic_platform_optimization']);
		add_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		add_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		add_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		add_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);
		add_filter('pre_option_automatic_platform_optimization_cache_by_device_type', [$this, 'automatic_platform_optimization_cache_by_device_type']);
		add_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'do_caching_mobile_files']);

		// PluginResolver gates cloudflare_plugin_subscriber out of the container at boot
		// (the official Cloudflare plugin isn't installed in this test environment), so its
		// display_apo_cache_notice callback was never wired to the event manager. Build the
		// subscriber directly (same approach Test_ExcludeDelayJs uses for Termly) and wire
		// it here, before isolating the hook, so the notice under test actually fires.
		$container            = apply_filters( 'rocket_container', null );
		$this->cloudflare     = new Cloudflare(
			$container->get( 'options' ),
			$container->get( 'options_api' ),
			$container->get( 'beacon' ),
			$container->get( 'cloudflare_plugin_facade' )
		);
		$this->event_manager = $container->get( 'event_manager' );
		$this->event_manager->add_subscriber( $this->cloudflare );

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'display_apo_cache_notice' );
	}

	public function tear_down()
	{
		remove_filter('pre_option_automatic_platform_optimization', [$this, 'automatic_platform_optimization']);
		remove_filter('pre_option_active_plugins', [$this, 'active_plugins']);
		remove_filter('pre_option_cloudflare_api_email', [$this, 'cloudflare_api_email']);
		remove_filter('pre_option_cloudflare_api_key', [$this, 'cloudflare_api_key']);
		remove_filter('pre_option_cloudflare_cached_domain_name', [$this, 'cloudflare_cached_domain_name']);
		remove_filter('pre_option_automatic_platform_optimization_cache_by_device_type', [$this, 'automatic_platform_optimization_cache_by_device_type']);
		remove_filter('pre_get_rocket_option_do_caching_mobile_files', [$this, 'do_caching_mobile_files']);

		$this->restoreWpHook( 'admin_notices' );

		$this->event_manager->remove_subscriber( $this->cloudflare );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected )
	{
		$this->config = $config;

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

	public function automatic_platform_optimization_cache_by_device_type() {
		return $this->config['cloudflare_mobile_cache'];
	}

	public function do_caching_mobile_files() {
		return $this->config['mobile_cache'];
	}

	public function automatic_platform_optimization() {
		return $this->config['automatic_platform_optimization'];
	}
}
