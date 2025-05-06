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

	private $option;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'maybe_enable_auto_preload_fonts', 9 );

	}

	public function tear_down() {
		$this->restoreWpHook( 'wp_rocket_upgrade' );
		delete_option( 'wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$options = get_option( 'wp_rocket_settings' );

		$options['preload_fonts'] = $config['options']['preload_fonts'];

		update_option( 'wp_rocket_settings', $options );

		do_action( 'wp_rocket_upgrade', $config['new'], $config['old'] );

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected['options'] as $key => $value ) {
			$this->assertArrayHasKey( $key, $expected['options'] );
			$this->assertSame( $value, $expected['options'][ $key ] );
		}

	}

}
