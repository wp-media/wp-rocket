<?php
/**
 * MCP Helpers.
 *
 * A small library, meant to be embedded in a WordPress plugin, that stores
 * "filter name -> PHP callback" mappings in a custom table, exposes CRUD over
 * those mappings as WordPress Abilities (WP 6.9+ Abilities API), and wires the
 * stored callbacks onto their filters at runtime.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Classes are loaded by Composer (PSR-4). This file is itself registered via the
// `files` autoload in composer.json, so the PSR-4 autoloader is already active by
// the time bootstrap() runs.

/**
 * Bootstraps the library.
 *
 * Call this once from the host plugin (e.g. on `plugins_loaded`). It is safe to
 * call more than once; wiring only happens on the first call.
 *
 * The host plugin is responsible for running the table installation on its own
 * activation hook, e.g.:
 *
 *     register_activation_hook( __FILE__, function () {
 *         ( new \MCPHelpers\Table\FilterCallbackTable() )->install();
 *     } );
 *
 * @return Plugin The bootstrapped instance.
 */
function bootstrap(): Plugin {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new Plugin();
		$plugin->init();
	}

	return $plugin;
}
