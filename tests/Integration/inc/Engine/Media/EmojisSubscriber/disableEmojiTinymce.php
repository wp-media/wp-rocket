<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmojisSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmojisSubscriber::disable_emoji_tinymce
 *
 * @group Media
 * @group Emojis
 */
class disableEmojiTinymce extends TestCase {
    public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
        remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );
    }
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $plugins, $expected ) {
        $GLOBALS['wp'] = (object) [
            'query_vars' => [],
            'request'    => 'http://example.org',
        ];

        if ( $config['bypass'] ) {
            $GLOBALS['wp']->query_vars['nowprocket'] = 1;
        }

        $this->emoji_option = $config['options']['emoji'];

		add_filter( 'pre_get_rocket_option_emoji', [ $this, 'set_emoji_value' ] );

		$this->assertSame(
			array_values( $expected ),
			array_values( apply_filters( 'tiny_mce_plugins', $plugins ) )
		);
    }

    public function set_emoji_value() {
        return $this->emoji_option;
    }
}
