<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\PreloadFonts\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreloadFonts\Admin\Subscriber::maybe_clear_preload_fonts_values
 *
 * @group PreloadFonts
 * @group AdminOnly
 * @group Media
 */
class Test_MaybeClearPreloadFontsValues extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->setUpSettings();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'maybe_clear_preload_fonts_values' );

	}

	public function tear_down() {
		$this->tearDownSettings();
		$this->restoreWpHook( 'wp_rocket_upgrade' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->mergeExistingSettingsAndUpdate( $config['options'] );

		do_action( 'wp_rocket_upgrade', $config['new'], $config['old'] );

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected['options'] as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[ $key ] );
		}
	}

}
