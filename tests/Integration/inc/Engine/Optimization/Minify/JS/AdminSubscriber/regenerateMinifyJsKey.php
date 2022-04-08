<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\JS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\JS\AdminSubscriber::regenerate_minify_js_key
 * @uses   ::create_rocket_uniqid
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_RegenerateMinifyJsKey extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testRegenerateMinifyCssKey( $settings, $expected, $should_run ) {
		$this->mergeExistingSettingsAndUpdate( $settings );

		$options = get_option( 'wp_rocket_settings', [] );

		if ( $should_run ) {
			$this->assertArrayHasKey( 'minify_js_key', $options );
			$this->assertEquals( strlen( '5ea8b0bf1b875099188739' ), strlen( $options['minify_js_key'] ) );
			unset( $expected['minify_js_key'] );
		}

		foreach ( $expected as $key => $value ) {
			$this->assertSame( $value, $options[ $key ] );
		}
	}
}
