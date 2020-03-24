<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group ThirdParty
 * @group WithAmp
 */
class Test_DisableOptionsOnAmp extends TestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;
	/**
	 * Instance of the post.
	 * @var WP_Post
	 */
	private static $post_id;

	/**
	 * Set up the User ID & current page before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'editor' ] );
		self::$post_id = $factory->post->create( [ 'post_author' => self::$user_id, ] );
		// Set global for WP<5.2 where get_the_content() doesn't take the $post parameter.
		$GLOBALS['post'] = get_post( self::$post_id );
		setup_postdata( self::$post_id );
	}

	public function testShouldBailoutIfIsNotAmpEndpoint() {
		$this->assertTrue( (bool) has_filter( 'wp_resource_hints', 'rocket_dns_prefetch') );
		do_action( 'wp' );
		$this->assertTrue( (bool) has_filter( 'wp_resource_hints', 'rocket_dns_prefetch') );
	}

	public function testShouldDisableOptionForAmpExceptImageSrcSet() {
		global $wp_filter;
		add_theme_support( \AMP_Theme_Support::SLUG );
		$this->assertTrue( (bool) has_filter( 'wp_resource_hints', 'rocket_dns_prefetch') );
		$this->assertFalse( (bool) has_filter( 'do_rocket_lazyload', '__return_false') );
		$this->assertFalse( empty( $wp_filter['rocket_buffer'] ) );
		do_action( 'wp' );
		$this->assertFalse( (bool) has_filter( 'wp_resource_hints', 'rocket_dns_prefetch') );
		$this->assertTrue( (bool) has_filter( 'do_rocket_lazyload', '__return_false') );
		$this->assertTrue( empty( $wp_filter['rocket_buffer'] ) );
	}
}
