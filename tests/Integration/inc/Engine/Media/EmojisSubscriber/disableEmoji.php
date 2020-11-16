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
	public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );
		$this->restoreWpFilter( 'init' );
	}

	/**
	 * @dataProvider configTestData
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
