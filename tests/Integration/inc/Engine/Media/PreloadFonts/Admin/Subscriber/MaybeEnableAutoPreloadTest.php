<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\PreloadFonts\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreloadFonts\Admin\Subscriber::maybe_enable_auto_preload_fonts
 *
 * @group AdminOnly
 * @group PreloadFonts
 * @group PreloadFontsAdminSubscriber
 */
class Test_MaybeEnableAutoPreloadFonts extends TestCase {

//	private $option;

	public function set_up() {
		parent::set_up();

		$this->setUpSettings();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'maybe_enable_auto_preload_fonts', 9 );

	}

	public function tear_down() {
		$this->tearDownSettings();

		$this->restoreWpHook( 'wp_rocket_upgrade' );

//		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'set_option'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->mergeExistingSettingsAndUpdate( $config['options'] );

//		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'set_option'] );

		do_action( 'wp_rocket_upgrade', $config['new'], $config['old'] );

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected['options'] as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[ $key ] );
		}

	}
//	public function set_option() {
//		return $this->option;
//	}
}
