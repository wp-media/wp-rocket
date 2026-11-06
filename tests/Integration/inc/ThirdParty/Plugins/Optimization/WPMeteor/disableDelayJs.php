<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Optimization\WPMeteor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor::disable_delay_js
 *
 * @group WPMeteor
 * @group ThirdParty
 */
class Test_DisableDelayJs extends TestCase {
	public function testShouldReturnExpected() {
		do_action( 'activate_wp-meteor/wp-meteor.php' );

		$options = get_option( 'wp_rocket_settings' );

		$this->assertSame(
			0,
			$options['delay_js']
		);
	}
}
