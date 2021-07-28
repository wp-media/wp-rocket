<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Ads;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ads\Adthrive::add_delay_js_exclusion
 *
 * @group Adthrive
 * @group ThirdParty
 */
class Test_AddDelayJsExclusion extends TestCase {
	private $delay_js;
	private $delay_js_exclusions;

	public function tearDown(): void {
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
		remove_filter( 'pre_get_rocket_option_delay_js_exclusions', [ $this, 'set_delay_js_exclusions' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$this->delay_js = $settings['delay_js'];
		$this->delay_js_exclusions = $settings['delay_js_exclusions'];

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
		add_filter( 'pre_get_rocket_option_delay_js_exclusions', [ $this, 'set_delay_js_exclusions' ] );

		do_action( 'activate_adthrive-ads/adthrive-ads.php' );

		$options = get_option( 'wp_rocket_settings' );

		$this->assertSame(
			$options,
			$expected
		);
	}

	public function set_delay_js() {
		return $this->delay_js;
	}

	public function set_delay_js_exclusions() {
		return $this->delay_js_exclusions;
	}
}
