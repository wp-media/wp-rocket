<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\EmojisSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Emojis\EmojisSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmojisSubscriber::disable_emoji_tinymce
 *
 * @group Media
 * @group Emojis
 */
class disableEmojiTinymce extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $plugins, $expected ) {
		Functions\expect( 'rocket_bypass' )
			->once()
			->andReturn( $config['bypass'] );

		$options = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->andReturn( $config['options']['emoji'] );

		$emojis  = new EmojisSubscriber( $options );

		$this->assertSame(
			array_values( $expected ),
			array_values( $emojis->disable_emoji_tinymce( $plugins ) )
		);
	}
}
