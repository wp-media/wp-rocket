<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\EmbedsSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Embeds\EmbedsSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::remove_embeds_rewrite_rules
 *
 * @group Media
 * @group Embeds
 */
class RemoveEmbedsRewriteRules extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $rules, $expected ) {
		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( $config['bypass'] );

		$options = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->andReturn( $config['options']['embeds'] );

		$embeds = new EmbedsSubscriber( $options );

		$this->assertSame(
			array_values( $expected ),
			array_values( $embeds->remove_embeds_rewrite_rules( $rules ) )
		);
	}
}
