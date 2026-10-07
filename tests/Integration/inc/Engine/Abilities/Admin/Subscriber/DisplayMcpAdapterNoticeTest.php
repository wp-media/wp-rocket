<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Admin\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Admin\Subscriber::display_mcp_adapter_notice()
 *
 * @group Abilities
 * @group AdminOnly
 */
class DisplayMcpAdapterNoticeTest extends AdminTestCase {
	/**
	 * Capabilities this test added to the administrator role.
	 *
	 * @var string[]
	 */
	private $added_caps = [];

	/**
	 * Keeps only the tested admin_notices callback.
	 */
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'display_mcp_adapter_notice' );
	}

	/**
	 * Restores admin_notices callbacks and the administrator role.
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
	 * @param string $expected  Expected notice HTML.
	 */
	public function testShouldDisplayExpected( string $role, array $user_meta, string $expected ): void {
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

		if ( '' === $expected ) {
			$this->assertSame( '', $this->get_actual_html() );

			return;
		}

		$this->assertStringContainsString( $this->format_the_html( $expected ), $this->get_actual_html() );
	}

	/**
	 * Gets the admin_notices output.
	 *
	 * @return string
	 */
	private function get_actual_html(): string {
		ob_start();
		do_action( 'admin_notices' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		return $this->format_the_html( (string) ob_get_clean() );
	}
}
