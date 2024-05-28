<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::activate_safe_mode
 *
 * @group  DeactivationIntent
 * @group  AdminOnly
 */
class Test_ActivateSafeMode extends TestCase {
	protected static $use_settings_trait = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldResetOptions( $expected ) {
		$container = apply_filters( 'rocket_container', null );

		$deactivation = $container->get( 'deactivation_intent' );

		$deactivation->activate_safe_mode();

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[ $key ] );
		}
	}
}
