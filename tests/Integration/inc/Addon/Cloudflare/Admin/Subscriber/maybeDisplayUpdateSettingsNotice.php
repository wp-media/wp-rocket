<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Admin\Subscriber::maybe_display_update_settings_notice
 *
 * @group CloudflareAdmin
 */
class TestMaybeDisplayUpdateSettingsNotice extends TestCase {
	use HttpRequestTrait;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_update_settings_notice', 10 );

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );
		$this->restoreWpHook( 'current_screen' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		if ( $config['cap'] ) {
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );
		set_current_screen( $config['current_screen']->id );

		if ( false !== $config['transient'] ) {
			set_transient( $user_id . '_cloudflare_update_settings', $config['transient'] );
		}

		$this->assertStringContainsStringIgnoringCase(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}
}
