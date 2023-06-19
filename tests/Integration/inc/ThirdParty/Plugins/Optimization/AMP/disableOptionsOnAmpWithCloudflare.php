<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Theme_Support;;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group WithAmpAndCloudflare
 */
class Test_DisableOptionsOnAmpWithCloudflare extends TestCase {
	private static $user_id = 0;
	private static $post_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'editor' ] );
		self::$post_id = $factory->post->create( [ 'post_author' => self::$user_id, ] );
		// Set global for WP<5.2 where get_the_content() doesn't take the $post parameter.
		$GLOBALS['post'] = get_post( self::$post_id );
		setup_postdata( self::$post_id );
	}

	public function testShouldDisableOptionForAmpWhenCloudflareEnabled() {
		global $wp_filter;
		add_theme_support( AMP_Theme_Support::SLUG );
		$this->assertNotFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
		$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertFalse( has_filter( 'do_rocket_lazyload_iframes', '__return_false' ) );
		$this->assertArrayHasKey( 'rocket_buffer', $wp_filter );
		$this->assertFalse( has_filter( 'pre_get_cloudflare_protocol_rewrite', '__return_false' ) );
		$this->assertFalse( has_filter( 'do_rocket_protocol_rewrite', '__return_false' ) );

		do_action( 'wp' );

		$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
		$this->assertNotFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertNotFalse( has_filter( 'do_rocket_lazyload_iframes', '__return_false' ) );
		$this->assertArrayNotHasKey( 'rocket_buffer', $wp_filter );
		$this->assertNotFalse( has_filter( 'pre_get_cloudflare_protocol_rewrite', '__return_false' ) );
		$this->assertNotFalse( has_filter( 'do_rocket_protocol_rewrite', '__return_false' ) );
	}
}
