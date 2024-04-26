<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPEngine::add_footprint
 * @uses   ::rocket_get_constant
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_AddFootprint extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_footprint', 50 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

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
		return $this->getTestData( __DIR__ . '/Int', 'addFootprint' );
	}
}
