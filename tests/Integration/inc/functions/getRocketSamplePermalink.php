<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_sample_permalink
 * @group Functions
 * @group Posts
 * @group thisone
 */
class Test_GetRocketSamePermalink extends TestCase {

	public function testShouldBailOutWhenPostDoesNotExist() {
		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( -1 )
		);

		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( 0, 'Lorem ipsum' )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnSamplePermalink( $config, $post_data, $expected ) {
		$this->set_permalink_structure( $config['structure'] );
		$post_id = $this->factory->post->create( $post_data );

		$actual = get_rocket_sample_permalink( $post_id, $config['override_post_title'], $config['override_post_name'] );

		$this->assertSame( $expected, $actual );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketSamplePermalink' );
	}
}
