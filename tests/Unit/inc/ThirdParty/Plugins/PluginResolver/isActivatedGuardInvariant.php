<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Architecture test protecting the centralized is_plugin_active() availability
 * guard in \WP_Rocket\ThirdParty\Plugins\PluginResolver::filter_active_registry.
 *
 * That guard is only invoked once, ahead of the sole static call to
 * PluginCompatibilityInterface::is_activated() among registry classes. This
 * scans production code so any future `::is_activated(` caller that bypasses
 * PluginResolver (and its guard) trips this test instead of silently relying
 * on a comment.
 *
 * @group  Plugins
 * @group  ThirdParty
 */
class Test_IsActivatedGuardInvariant extends TestCase {
	/**
	 * Only PluginResolver::filter_active_registry() may call ::is_activated() statically.
	 */
	public function testOnlyPluginResolverCallsIsActivatedStatically() {
		$inc_dir = dirname( __DIR__, 6 ) . '/inc';

		$this->assertDirectoryExists( $inc_dir, "Could not resolve inc/ relative to the test file: {$inc_dir}" );

		$offending_files = [];

		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $inc_dir, RecursiveDirectoryIterator::SKIP_DOTS ) );

		foreach ( $iterator as $file ) {
			if ( 'php' !== strtolower( $file->getExtension() ) ) {
				continue;
			}

			$contents = file_get_contents( $file->getPathname() );

			if ( false === $contents || false === strpos( $contents, '::is_activated(' ) ) {
				continue;
			}

			$offending_files[] = str_replace( $inc_dir . '/', 'inc/', $file->getPathname() );
		}

		$this->assertSame(
			[ 'inc/ThirdParty/Plugins/PluginResolver.php' ],
			$offending_files,
			'A new caller of ::is_activated() was found outside PluginResolver::filter_active_registry(). ' .
			'is_plugin_active() is only unconditionally available from WP 6.8+; any new caller must either ' .
			"route through PluginResolver::get_active_plugins() or add its own function_exists( 'is_plugin_active' ) guard."
		);
	}
}
