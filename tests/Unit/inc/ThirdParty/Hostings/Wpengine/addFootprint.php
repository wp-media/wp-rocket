<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::add_footprint
 * @uses   ::rocket_get_constant
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_AddFootprint extends WpengineTestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddFootprint( $white_label_footprint, $html, $expected ) {
		if ( $white_label_footprint ) {
			$this->white_label_footprint = $white_label_footprint;
		}
		$this->assertSame(
			$expected,
			$this->wpengine->add_footprint( $html )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addFootprint' );
	}
}
