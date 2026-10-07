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
	 * Capabilities this test added to the administrator role.
	 *
	 * @var string[]
	 */
	private $added_caps = [];

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

		foreach ( $this->added_caps as $cap ) {
			$this->removeRoleCap( 'administrator', $cap );
		}

		$this->added_caps = [];

		parent::tear_down();
	}

	/**
	 * Checks the notice is rendered only for MCP users who can install plugins, when no MCP Adapter is loaded.
	 *
	 * @dataProvider configTestData
	 *
	 * @param string $role      User role.
	 * @param array  $user_meta User meta to add.
	 * @param bool   $expected  Whether the notice is expected.
	 */
	public function testShouldDisplayExpected( string $role, array $user_meta, bool $expected ): void {
		if ( class_exists( 'WP\MCP\Core\McpAdapter' ) ) {
			$this->markTestSkipped( 'An MCP Adapter is loaded in the test environment.' );
		}

		foreach ( [ 'install_plugins', 'rocket_manage_options' ] as $cap ) {
			if ( ! get_role( 'administrator' )->has_cap( $cap ) ) {
				$this->setRoleCap( 'administrator', $cap );
				$this->added_caps[] = $cap;
			}
		}

		$this->setCurrentUser( $role );

		foreach ( $user_meta as $key => $value ) {
			update_user_meta( $this->user_id, $key, $value );
		}

		$this->assertNotFalse( has_action( 'admin_notices', [ apply_filters( 'rocket_container', null )->get( 'abilities_admin_subscriber' ), 'display_mcp_adapter_notice' ] ) );

		ob_start();
		do_action( 'admin_notices' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$output = (string) ob_get_clean();

		if ( $expected ) {
			$this->assertStringContainsString( 'plugin-install.php?s=mcp-adapter&#038;tab=search&#038;type=term', $output );
			$this->assertStringContainsString( 'box=mcp_adapter_notice', $output );
		} else {
			$this->assertSame( '', trim( $output ) );
		}
	}
}
