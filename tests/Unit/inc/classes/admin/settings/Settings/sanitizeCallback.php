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
	public function testShouldSanitizeCriticalCss( $original, $sanitized ) {
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

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$settings          = new Settings( $this->createMock( 'WP_Rocket\Admin\Options_Data' ) );
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
