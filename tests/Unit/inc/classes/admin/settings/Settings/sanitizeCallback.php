<?php

namespace WP_Rocket\Tests\Unit\inc\classes\admin\settings\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Settings\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Admin\Settings::sanitize_callback
 * @group  Admin
 * @group  Settings
 */
class Test_SanitizeCallback extends TestCase {
	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldSanitize( $original, $sanitized ) {
		Functions\when( 'wp_strip_all_tags' )->alias( function( $string, $remove_breaks ) {
			$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$string = strip_tags( $string );

			if ( $remove_breaks ) {
				$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
			}

			return trim( $string );
		} );

		Functions\when( 'sanitize_email' )->alias( function( $email ) {
			return $email;
		} );

		Functions\when( 'sanitize_text_field' )->alias( function( $text ) {
			return $text;
		} );

		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[ 'defer_all_js', 0, 1 ],
			[ 'defer_all_js_safe', 0, 1 ],
			[ 'purge_cron_interval', 0, 0 ],
			[ 'purge_cron_unit', 0, 'HOUR_IN_SECONDS' ],
			[ 'schedule_automatic_cleanup', 0, 0 ],
			[ 'automatic_cleanup_frequency', 'daily', 'weekly' ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$settings          = new Settings( $options );
		$sanitize_callback = $settings->sanitize_callback( $original );

		// this works
		$this->assertSame(
			$sanitized['critical_css'],
			$sanitize_callback['critical_css']
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'sanitizeCallback' );
	}
}
