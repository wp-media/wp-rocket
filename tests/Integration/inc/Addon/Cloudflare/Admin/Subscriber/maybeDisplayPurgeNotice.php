<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Admin\Subscriber::maybe_display_purge_notice
 *
 * @group CloudflareAdmin
 */
class TestMaybeDisplayPurgeNotice extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_purge_notice', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_purge_cloudflare_cache' );

		if ( $config['cap'] ) {
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		set_current_screen( 'settings_page_wprocket' );
		wp_set_current_user( $user_id );

		if ( false !== $config['transient'] ) {
			set_transient( $user_id . '_cloudflare_purge_result', $config['transient'] );
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
