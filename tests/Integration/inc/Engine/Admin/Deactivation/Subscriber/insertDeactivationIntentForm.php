<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::insert_deactivation_intent_form
 *
 * @group  AdminOnly
 * @group  DeactivationIntent
 */
class Test_InsertDeactivationIntentForm extends TestCase {
	public function tear_down() {
		delete_option( 'wp_rocket_hide_deactivation_form' );
		delete_transient( 'rocket_hide_deactivation_form' );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->set_snooze( $config );

		ob_start();
		do_action( 'admin_footer-plugins.php' );
		$actual = ob_get_clean();

		if ( ! $expected ) {
			$this->assertStringNotContainsString( 'wpr-deactivation-modal', $actual );
		} else {
			$this->assertStringContainsString( 'wpr-deactivation-modal', $actual );
		}
	}

	private function set_snooze( $config ) {
		if ( ! empty( $config['option'] ) ) {
			add_option( 'wp_rocket_hide_deactivation_form', $config['option'] );
		}

		if ( ! empty( $config['transient'] ) ) {
			set_transient( 'rocket_hide_deactivation_form', $config['transient'], DAY_IN_SECONDS );
		}
	}
}
