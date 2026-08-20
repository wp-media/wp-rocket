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
	 * On current WordPress core, _wp_filter_build_unique_id() returns
	 * (string) spl_object_id( $callback ) for a Closure/invokable object
	 * registered directly on a hook. That string is purely numeric, so PHP
	 * casts it to an int array key in WP_Hook::$callbacks. Rather than
	 * relying on core's callback-ID algorithm (which has changed across
	 * versions), this test forges that int key directly so the regression
	 * is verified independently of the WP version running the suite.
	 * unregister_callback() must not fatal (substr() expects a string) when
	 * it iterates over such a key.
	 */
	public function testShouldNotFatalWhenAnotherCallbackHasIntKey() {
		global $wp_filter;

		add_action( 'transition_post_status', '__return_null', PHP_INT_MAX );

		$idx = 12345;

		$wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ][ $idx ] = [
			'function'      => function () {},
			'accepted_args' => 3,
		];

		$container  = apply_filters( 'rocket_container', null );
		$cloudflare = $container->get( 'cloudflare_plugin_subscriber' );

		// Prior to the fix this call fatals with:
		// TypeError: substr(): Argument #1 ($string) must be of type string, int given.
		$cloudflare->unregister_cloudflare_clean_on_post();

		$this->assertArrayHasKey(
			$idx,
			$wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ],
			'The unrelated int-keyed callback must be left untouched.'
		);

		remove_action( 'transition_post_status', '__return_null', PHP_INT_MAX );
		unset( $wp_filter['transition_post_status']->callbacks[ PHP_INT_MAX ][ $idx ] );
	}
}
