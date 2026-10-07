<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::unregister_callback
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class TestUnregisterCallback extends TestCase {

	/**
	 * unregister_cloudflare_clean_on_post() is guarded behind is_plugin_active(), which
	 * requires the Cloudflare plugin to be active and its credentials to be set. Stub
	 * all four so the guard passes through and this test still reaches the removal
	 * logic it's actually meant to cover.
	 */
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

	public function plugin_enabled( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			$plugins = (array) $plugins;
		}

		$plugins[] = 'cloudflare/cloudflare.php';

		return $plugins;
	}

	public function cloudflare_api_email() {
		return 'test@example.com';
	}

	public function cloudflare_api_key() {
		return 'test-api-key';
	}

	public function cloudflare_cached_domain_name() {
		return 'example.com';
	}

	/**
	 * On current WordPress core, _wp_filter_build_unique_id() returns
	 * (string) spl_object_id( $callback ) for a Closure/invokable object
	 * registered directly on a hook. That string is purely numeric, so PHP
	 * casts it to an int array key in WP_Hook::$callbacks. Rather than
	 * relying on core's callback-ID algorithm (which has changed across
	 * versions), this test forges the callback key directly so the
	 * regression is verified independently of the WP version running the
	 * suite. unregister_callback() must not fatal (substr() expects a
	 * string) when it iterates over an int key, and must still remove only
	 * the callbacks whose key matches the target method name.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Fixture config; 'key' is the callback array key to forge.
	 * @param array $expected Fixture expectation; 'removed' is whether the key should be gone.
	 */
	public function testShouldNotFatalAndShouldOnlyRemoveMatchingCallback( $config, $expected ) {
		global $wp_filter;

		$wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ][ $config['key'] ] = [
			'function'      => static function () {},
			'accepted_args' => 3,
		];

		$container  = apply_filters( 'rocket_container', null );
		$cloudflare = $container->get( 'cloudflare_plugin_subscriber' );

		// Prior to the fix, an int $key fatals here with:
		// TypeError: substr(): Argument #1 ($string) must be of type string, int given.
		$cloudflare->unregister_cloudflare_clean_on_post();

		$this->assertSame(
			! $expected['removed'],
			isset( $wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ][ $config['key'] ] )
		);

		unset( $wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ][ $config['key'] ] );
	}
}
