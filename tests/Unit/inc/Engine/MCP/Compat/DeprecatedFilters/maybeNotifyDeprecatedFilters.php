<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Compat\DeprecatedFilters;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\MCP\Compat\DeprecatedFilters;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\MCP\Compat\DeprecatedFilters::maybe_notify_deprecated_filters
 *
 * @covers \WP_Rocket\Engine\MCP\Compat\DeprecatedFilters::maybe_notify_deprecated_filters
 *
 * @group MCP
 */
class Test_MaybeNotifyDeprecatedFilters extends TestCase {

	/**
	 * A deprecation notice must fire for the legacy server-enabled filter when a
	 * callback is registered on it, pointing to the library replacement.
	 *
	 * @return void
	 */
	public function testShouldEmitNoticeWhenLegacyServerEnabledFilterHasCallback() {
		Functions\when( 'has_filter' )->alias(
			function ( $hook ) {
				return 'rocket_mcp_oauth_server_enabled' === $hook;
			}
		);

		Functions\expect( '_deprecated_hook' )
			->once()
			->with( 'rocket_mcp_oauth_server_enabled', '3.24', 'wpmedia_mcp_oauth_server_enabled' );

		( new DeprecatedFilters() )->maybe_notify_deprecated_filters();
	}

	/**
	 * A deprecation notice must fire for the legacy trusted-publishers filter when
	 * a callback is registered on it, pointing to the library replacement.
	 *
	 * @return void
	 */
	public function testShouldEmitNoticeWhenLegacyTrustedPublishersFilterHasCallback() {
		Functions\when( 'has_filter' )->alias(
			function ( $hook ) {
				return 'rocket_mcp_trusted_publishers' === $hook;
			}
		);

		Functions\expect( '_deprecated_hook' )
			->once()
			->with( 'rocket_mcp_trusted_publishers', '3.24', 'wpmedia_mcp_oauth_trusted_publishers' );

		( new DeprecatedFilters() )->maybe_notify_deprecated_filters();
	}

	/**
	 * No deprecation notice must fire when no legacy filter has a callback (the
	 * default, when no third party opts into the legacy filters).
	 *
	 * @return void
	 */
	public function testShouldNotEmitNoticeWhenNoLegacyFilterHasCallback() {
		Functions\when( 'has_filter' )->justReturn( false );

		Functions\expect( '_deprecated_hook' )->never();

		( new DeprecatedFilters() )->maybe_notify_deprecated_filters();
	}
}
