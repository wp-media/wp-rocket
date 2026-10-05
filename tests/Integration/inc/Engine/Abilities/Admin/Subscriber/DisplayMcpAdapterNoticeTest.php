<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Admin\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Admin\Subscriber::display_mcp_adapter_notice()
 *
 * @group Abilities
 */
class DisplayMcpAdapterNoticeTest extends AdminTestCase {
	/**
	 * Loads the notice helpers.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		if ( ! function_exists( 'rocket_notice_html' ) ) {
			require_once WP_ROCKET_INC_PATH . 'admin/ui/notices.php';
		}
	}

	/**
	 * Keeps only the tested admin_notices callback.
	 */
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'display_mcp_adapter_notice' );
	}

	/**
	 * Restores admin_notices callbacks.
	 */
	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );

		parent::tear_down();
	}

	/**
	 * Checks the subscriber is hooked and renders nothing for users without MCP usage.
	 *
	 * @dataProvider configTestData
	 *
	 * @param string $role User role.
	 */
	public function testShouldNotDisplayNoticeForUserWithoutMcpUsage( string $role ): void {
		$this->setCurrentUser( $role );

		$this->assertNotFalse( has_action( 'admin_notices', [ apply_filters( 'rocket_container', null )->get( 'abilities_admin_subscriber' ), 'display_mcp_adapter_notice' ] ) );

		ob_start();
		do_action( 'admin_notices' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$this->assertSame( '', trim( (string) ob_get_clean() ) );
	}
}
