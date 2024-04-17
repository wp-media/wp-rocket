<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Theme_Support;
use AMP_Options_Manager;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group ThirdParty
 * @group WithAmp
 */
class Test_DisableOptionsOnAmp extends TestCase {
	protected $path_to_test_data = 'disableOptionsOnAmpIntegration.php';
	private static $user_id      = 0;
	private static $post_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'editor' ] );
		self::$post_id = $factory->post->create( [ 'post_author' => self::$user_id, ] );
		// Set global for WP<5.2 where get_the_content() doesn't take the $post parameter.
		$GLOBALS['post'] = get_post( self::$post_id );
		setup_postdata( self::$post_id );
	}

	/**
	 * @dataProvider ampDataProvider
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( $expected[ 'bailout' ] ) {
			$this->assertNotFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
		} else {
			global $wp_filter;

			add_theme_support( AMP_Theme_Support::SLUG );
			$this->assertNotFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
			$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
			$this->assertFalse( has_filter( 'do_rocket_lazyload_iframes', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_async_css', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_delay_js', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_preload_links', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_minify_js', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_lazyload_css_bg_img', '__return_false' ) );

			$this->assertArrayHasKey( 'rocket_buffer', $wp_filter );

			if ( ! is_null( $config[ 'amp_options' ] ) ) {
				$this->setSettings( 'theme_support', $config[ 'amp_options' ]['theme_support'] );
				$options = get_option( AMP_Options_Manager::OPTION_NAME );
				$this->assertEquals( $config[ 'amp_options' ]['theme_support'], $options['theme_support'] );
			} else {
				delete_option( AMP_Options_Manager::OPTION_NAME );
			}
		}

		do_action( 'wp' );

		if ( $expected[ 'bailout' ] ) {
			$this->assertNotFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
		} else {
			$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
			$this->assertNotFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'do_rocket_lazyload_iframes', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'pre_get_rocket_option_async_css', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'pre_get_rocket_option_delay_js', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'pre_get_rocket_option_preload_links', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'pre_get_rocket_option_minify_js', '__return_false' ) );
			$this->assertNotFalse( has_filter( 'pre_get_rocket_option_lazyload_css_bg_img', '__return_false' ) );

			if ( in_array( $config[ 'amp_options' ][ 'theme_support' ], [ 'transitional', 'reader' ], true ) ) {
				$this->assertArrayHasKey( 'rocket_buffer', $wp_filter );
			} else {
				$this->assertArrayNotHasKey( 'rocket_buffer', $wp_filter );
			}
		}
	}
}
