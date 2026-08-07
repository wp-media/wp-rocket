<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts\Admin\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber::enable_google_fonts
 * @uses   \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings::enable_google_fonts
 *
 * @group  AdminOnly
 * @group  GoogleFonts
 */
class Test_EnableGoogleFonts extends AjaxTestCase {
	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::removeDBHooks();
	}

	public function set_up() {
		parent::set_up();

		$options                        = get_option( 'wp_rocket_settings', [] );
		$options['minify_google_fonts'] = 0;
		update_option( 'wp_rocket_settings', $options );

		remove_action( 'admin_init', 'rocket_upgrader' );
	}

	public function tear_down() {
		add_action( 'admin_init', 'rocket_upgrader' );

		parent::tear_down();
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testShouldCallEnableGoogleFonts( $is_user_auth ) {
		$this->action = 'rocket_enable_google_fonts';

		if ( $is_user_auth ) {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		} else {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		}

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );

		$this->expectException( 'WPAjaxDieContinueException' );

		$this->_handleAjax( $this->action );

		$response = json_decode( $this->_last_response );

		$options   = get_option( 'wp_rocket_settings' );
		$gf_minify = $options['minify_google_fonts'];

		if ( $is_user_auth ) {
			$this->assertTrue( $response->success );
			$this->assertEquals( 1, $gf_minify );
		} else {
			$this->assertFalse( $response->success );
			$this->assertEquals( 0, $gf_minify );
		}
	}

	public function provideTestData() {
		return $this->getTestData( __DIR__, 'enableGoogleFonts' );
	}
}
