<?php

namespace WP_Rocket\Tests\Integration\inc\classes\admin\settings\Settings;

use WPMedia\PHPUnit\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Admin\Settings::sanitize_callback
 * @group  AdminOnly
 * @group  Settings
 */
class Test_SanitizeCallback extends AdminTestCase {

	public function setUp() {
		parent::setUp();

		remove_action( 'admin_init', 'send_frame_options_header' );
	}

	public function tearDown() {
		parent::tearDown();

		add_action( 'admin_init', 'send_frame_options_header' );
	}

	public function testSettingsAreRegistered() {
		$registered = get_registered_settings();
		$this->assertArrayHasKey( 'wp_rocket_settings', $registered );

		$args = $registered['wp_rocket_settings'];
		$this->assertEquals( 'wprocket', $args['group'] );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldSanitize( $original, $sanitized ) {
		$this->assertSame(
			$sanitized,
			apply_filters( 'sanitize_option_wp_rocket_settings', $original )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'sanitizeCallback' );
	}
}
