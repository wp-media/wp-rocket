<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber::display_google_fonts_enabler
 * @uses   \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings::display_google_fonts_enabler
 *
 * @group  AdminOnly
 * @group  GoogleFonts
 */
class Test_DisplayGoogleFontsEnabler extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_settings_tools_content', 'display_google_fonts_enabler' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'setGoogleFontsOption' ] );

		$this->restoreWpHook( 'rocket_settings_tools_content' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCallSettingsFontsEnabler( $config, $expected ) {
		if ( $config['is-user-auth'] ) {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		} else {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		}

		if ( $config['is-gf-minify'] ) {
			add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'setGoogleFontsOption' ] );
		}

		set_current_screen( 'settings_page_wprocket' );

		$this->assertSame( $expected, $this->getActualHtml() );
	}

	public function setGoogleFontsOption() {
		return 1;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'displayGoogleFontsEnabler' );
	}


	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_settings_tools_content' );

		return $this->format_the_html( ob_get_clean() );
	}
}
