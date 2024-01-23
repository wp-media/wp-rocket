<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber::display_google_fonts_enabler
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

	private function getExpectedHtml() {
		$html = <<<HTML
<div id="wpr-mobile_cpcss_view" class="wpr-tools">
<div class="wpr-tools-col">
<div class="wpr-title3 wpr-tools-label wpr-icon-stack">
Enable Google Font Optimization</div>
<div class="wpr-field-description wpr-hide-on-click">
Improves font performance and combines multiple font requests to reduce the number of HTTP requests.</div>
<div class="wpr-field-description wpr-hide-on-click">
This is a one-time action and this button will be removed afterwards.<a href="https://docs.wp-rocket.me/article/1312-optimize-google-fonts" data-beacon-article="5e8687c22c7d3a7e9aea4c4a" target="_blank" rel="noopener noreferrer">
More info</a>
</div>
<div class="wpr-field-description wpr-field wpr-isHidden wpr-show-on-click">
Google Fonts Optimization is now enabled for your site.<a href="https://docs.wp-rocket.me/article/1312-optimize-google-fonts" data-beacon-article="5e8687c22c7d3a7e9aea4c4a" target="_blank" rel="noopener noreferrer">
More info</a>
</div>
</div>
<div class="wpr-tools-col">
<button id="wpr-action-rocket_enable_google_fonts" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
Optimize Google Fonts</button>
</div>
</div>
HTML;

		return $this->format_the_html( $html );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_settings_tools_content' );

		return $this->format_the_html( ob_get_clean() );
	}
}
