<?php
/**
 * Last resort autoloader for the vendor namespaces WP Rocket bundles without Mozart.
 *
 * Composer keeps its namespace prefix table in memory for the whole request. When
 * WordPress swaps the plugin directory in place during an update, a request that
 * started on the previous version keeps that table while the files on disk are
 * already the new ones. Any namespace the previous version did not know about is
 * then unresolvable, even though its files are present, which surfaces as a fatal
 * error while linking a bundled class.
 *
 * @since 3.23.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Namespace prefixes resolved by the fallback autoloader.
 *
 * Restricted to the bundled dependencies that keep their upstream namespace: the
 * Mozart prefixed ones live under a namespace only WP Rocket declares, so their
 * prefix is present in every version of the Composer table.
 *
 * The mapping is repeated here instead of being read back from
 * vendor/composer/autoload_psr4.php on purpose, since that file is precisely what
 * can be outdated in memory when this autoloader is needed.
 *
 * @since 3.23.2
 *
 * @return array<string,string> Namespace prefix as key, directory relative to the plugin root as value.
 */
function rocket_get_vendor_autoload_fallback_map(): array {
	return [
		'WP\\MCP\\'       => 'vendor/wordpress/mcp-adapter/includes/',
		'WP\\McpSchema\\' => 'vendor/wordpress/php-mcp-schema/src/',
	];
}

/**
 * Converts a class name into the path of the file declaring it, relative to the plugin root.
 *
 * @since 3.23.2
 *
 * @param string $class_name Fully qualified class name.
 *
 * @return string Relative path, or an empty string when the class is not one of the bundled dependencies.
 */
function rocket_get_vendor_class_relative_path( string $class_name ): string {
	foreach ( rocket_get_vendor_autoload_fallback_map() as $prefix => $directory ) {
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			continue;
		}

		$relative_class = substr( $class_name, strlen( $prefix ) );

		return $directory . str_replace( '\\', '/', $relative_class ) . '.php';
	}

	return '';
}

/**
 * Registers the fallback autoloader at the end of the SPL stack.
 *
 * Appended, so it is only consulted once every other autoloader has missed. A
 * copy of a bundled dependency provided by another plugin is therefore never
 * overridden: that plugin's autoloader resolves the class before this one runs.
 *
 * @since 3.23.2
 *
 * @return void
 */
function rocket_register_vendor_autoload_fallback(): void {
	static $registered = false;

	if ( $registered ) {
		return;
	}

	$registered = true;

	spl_autoload_register(
		function ( $class_name ) {
			$relative_path = rocket_get_vendor_class_relative_path( (string) $class_name );

			if ( '' === $relative_path ) {
				return;
			}

			$file = WP_ROCKET_PATH . $relative_path;

			if ( ! is_readable( $file ) ) {
				return;
			}

			require_once $file;
		},
		true,
		false
	);
}
