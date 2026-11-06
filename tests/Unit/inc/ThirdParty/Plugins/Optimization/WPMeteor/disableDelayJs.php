<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Optimization\WPMeteor;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor::disable_delay_js
 *
 * @group WPMeteor
 * @group ThirdParty
 */
class Test_DisableDelayJs extends TestCase {
	public function testShouldReturnExpected() {
		$meteor = new WPMeteor();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wp_rocket_settings', [] )
			->andReturn( [] );

		Functions\expect( 'update_option' )
			->once()
			->with( 'wp_rocket_settings', [
				'delay_js' => 0
			] );

		$meteor->disable_delay_js();
	}
}
