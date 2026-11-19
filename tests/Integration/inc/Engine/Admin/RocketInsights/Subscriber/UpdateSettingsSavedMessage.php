<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::update_settings_saved_message
 * 
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_UpdateSettingsSavedMessage extends TestCase {
	public function set_up() {
		parent::set_up();

		// Unregister all callbacks except the one we're testing
		$this->unregisterAllCallbacksExcept( 'rocket_settings_saved_message', 'update_settings_saved_message' );
	}

	public function tear_down() {
		// Restore the hook
		$this->restoreWpHook( 'rocket_settings_saved_message' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		add_filter( 'rocket_rocket_insights_enabled', $config['ri_is_enabled'] ? '__return_true' : '__return_false' );

		$message = wpm_apply_filters_typed( 'string', 'rocket_settings_saved_message', '' );

		if ( is_array( $expected ) ) {
			foreach ( $expected as $expected_string ) {
				$this->assertStringContainsString( $expected_string, $message );
			}

			return;
		}

		$this->assertSame( $expected, $message );
	}
}
