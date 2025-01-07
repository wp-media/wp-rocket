<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmojisSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\EmojisSubscriber::disable_emoji
 *
 * @group Media
 * @group Emojis
 */
class disableEmoji extends TestCase {
	private $emoji_option;

	public function tear_down() {
        parent::tear_down();

        unset( $_GET['nowprocket'] );
		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );
		$this->restoreWpHook( 'init' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {

        if ( $config['bypass'] ) {
            $_GET['nowprocket'] = 1;
		}

		$this->emoji_option = $config['options']['emoji'];

		add_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );

		$this->unregisterAllCallbacksExcept( 'init', 'disable_emoji' );

		do_action( 'init' );

		if ( $expected ) {
			$this->assertFalse( has_action( 'wp_head', 'print_emoji_detection_script' ) );
			$this->assertNotFalse( has_filter( 'emoji_svg_url', '__return_false' ) );
		} else {
			$this->assertNotFalse( has_action( 'wp_head', 'print_emoji_detection_script' ) );
			$this->assertFalse( has_filter( 'emoji_svg_url', '__return_false' ) );
		}
	}

	public function set_emoji_value() {
        return $this->emoji_option;
    }
}
