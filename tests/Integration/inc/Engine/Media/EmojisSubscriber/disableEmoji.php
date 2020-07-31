<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmojisSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmojisSubscriber::disable_emoji
 *
 * @group Media
 * @group Emojis
 */
class disableEmoji extends TestCase {
	private static $emojis;

	public static function setUpBeforeClass() {
		$container      = apply_filters( 'rocket_container', '' );
		static::$emojis = $container->get( 'emojis_subscriber' );
	}

	public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );
		$this->restoreWpFilter( 'init' );
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param [type] $config
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$GLOBALS['wp'] = (object) [
            'query_vars' => [],
            'request'    => 'http://example.org',
        ];

        if ( $config['bypass'] ) {
            $GLOBALS['wp']->query_vars['nowprocket'] = 1;
		}

		$this->emoji_option = $config['options']['emoji'];

		add_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );

		$this->unregisterAllCallbacksExcept( 'init', 'disable_emoji' );

		do_action( 'init' );

		if ( $expected ) {
			$this->assertFalse( has_action( 'wp_head', 'print_emoji_detection_script', 7 ) );
			$this->assertFalse( has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) );
			$this->assertFalse( has_filter( 'the_content_feed', 'wp_staticize_emoji' ) );
			$this->assertFalse( has_filter( 'comment_text_rss', 'wp_staticize_emoji' ) );
			$this->assertFalse( has_filter( 'wp_mail', 'wp_staticize_emoji_for_email' ) );
			$this->assertNotFalse( has_filter( 'emoji_svg_url', '__return_false' ) );
		} else {
			$this->assertNotFalse( has_action( 'wp_head', 'print_emoji_detection_script', 7 ) );
			$this->assertNotFalse( has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) );
			$this->assertNotFalse( has_filter( 'the_content_feed', 'wp_staticize_emoji' ) );
			$this->assertNotFalse( has_filter( 'comment_text_rss', 'wp_staticize_emoji' ) );
			$this->assertNotFalse( has_filter( 'wp_mail', 'wp_staticize_emoji_for_email' ) );
			$this->assertFalse( has_filter( 'emoji_svg_url', '__return_false' ) );
		}
	}

	public function set_emoji_value() {
        return $this->emoji_option;
    }
}
