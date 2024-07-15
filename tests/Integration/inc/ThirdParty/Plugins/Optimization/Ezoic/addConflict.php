<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Optimization\Ezoic;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\Ezoic::add_conflict
 *
 * @group Ezoic
 * @group ThirdParty
 */
class Test_AddConflict extends TestCase {
	public function testShouldReturnExpected() {
		$plugins  = [];
		$expected = [
			'ezoic' => 'ezoic-integration/ezoic-integration.php',
		];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_plugins_to_deactivate', $plugins )
		);
	}
}
