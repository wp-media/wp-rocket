<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::regenerate_minify_css_key
 *
 * @uses ::create_rocket_uniqid
 *
 * @group Optimize
 * @group Minify
 * @group AdminSubscriber
 * @group AdminOnly
 */
class Test_RegenerateMinifyCssKey extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'pre_update_option_wp_rocket_settings', 'regenerate_minify_css_key' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'pre_update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testRegenerateMinifyCssKey( $settings, $expected, $should_run ) {
		$this->mergeExistingSettingsAndUpdate( $settings );

		$options = get_option( 'wp_rocket_settings', [] );

		if ( $should_run ) {
			$this->assertArrayHasKey( 'minify_css_key', $options );
			$this->assertEquals( strlen( '5ea8b0bf1b875099188739' ), strlen( $options['minify_css_key'] ) );
			unset( $expected['minify_css_key'] );
		}

		foreach ( $expected as $key => $value ) {
			$this->assertSame( $value, $options[ $key ] );
		}
	}
}
