<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::add_footprint
 * @uses   ::rocket_get_constant
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_AddFootprint extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddFootprint( $white_label_footprint, $html, $expected ) {
		if ( $white_label_footprint ) {
			$this->white_label_footprint = $white_label_footprint;
		}
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $html )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addFootprint' );
	}
}
