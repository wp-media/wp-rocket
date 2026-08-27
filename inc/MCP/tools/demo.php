<?php
/**
 * MCP Helpers proof-of-concept demo driver.
 *
 * Drives three real WP-Rocket use cases through the actual abilities (the same
 * code path an MCP client / the REST /run endpoint uses), then shows the live
 * effect on each filter.
 *
 *   php inc/MCP/tools/demo.php up       create the demo entries + show effect
 *   php inc/MCP/tools/demo.php status   show the current entries + effect
 *   php inc/MCP/tools/demo.php down     remove the demo entries
 *
 * Run it inside the site's PHP environment. With WP-CLI:
 *   wp eval-file inc/MCP/tools/demo.php up
 * Running raw PHP against a Local site needs the MySQL socket, e.g.:
 *   php -d mysqli.default_socket=/path/to/mysqld.sock inc/MCP/tools/demo.php up
 *
 * NOTE: the abilities only register when the MCPHelpers autoloader is present.
 * If they're missing, run `composer dump-autoload` on this branch first.
 *
 * @package WP_Rocket\MCP
 */

// Bootstrap WordPress if not already loaded (e.g. plain `php`, not wp-cli).
if ( ! defined( 'ABSPATH' ) ) {
	$dir = __DIR__;
	while ( '/' !== $dir && ! file_exists( $dir . '/wp-load.php' ) ) {
		$dir = dirname( $dir );
	}
	if ( ! file_exists( $dir . '/wp-load.php' ) ) {
		fwrite( STDERR, "Could not locate wp-load.php.\n" );
		exit( 1 );
	}
	define( 'WP_USE_THEMES', false );
	require $dir . '/wp-load.php';
}

if ( 'cli' !== PHP_SAPI ) {
	fwrite( STDERR, "Run this from the command line.\n" );
	exit( 1 );
}

// Act as an administrator (the abilities require the manage capability).
if ( ! current_user_can( 'manage_options' ) ) {
	$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
	if ( $admins ) {
		wp_set_current_user( $admins[0]->ID );
	}
}

$create = wp_get_ability( 'mcp-helpers/create-filter-callback' );
$read   = wp_get_ability( 'mcp-helpers/read-filter-callbacks' );
$delete = wp_get_ability( 'mcp-helpers/delete-filter-callback' );

if ( ! $create || ! $read || ! $delete ) {
	fwrite( STDERR, "MCP Helpers abilities are not registered.\n" );
	fwrite( STDERR, "Run `composer dump-autoload` on this branch, then retry.\n" );
	exit( 1 );
}

$auth = 'Basic ' . base64_encode( 'staging:secret' ); // demo placeholder credential.

/*
 * Each demo: a title, the create-filter-callback payload(s), and a probe that
 * fires the affected filter(s) to show the live effect.
 */
$demos = [
	'1. Preload behind HTTP auth' => [
		'entries' => [
			[ 'rocket_preload_sitemap_request_args', 'rocket/set-array-key', [ 'key' => 'headers', 'value' => [ 'Authorization' => $auth ] ] ],
			[ 'rocket_partial_preload_url_request_args', 'rocket/set-array-key', [ 'key' => 'headers', 'value' => [ 'Authorization' => $auth ] ] ],
		],
		'probe'   => static function () {
			$args = apply_filters( 'rocket_preload_sitemap_request_args', [] );
			return 'preload request headers.Authorization = ' . ( $args['headers']['Authorization'] ?? '(none)' );
		},
	],
	'2. LazyLoad threshold' => [
		'entries' => [
			[ 'rocket_lazyload_threshold', 'rocket/return-int', [ 'value' => 600 ] ],
		],
		'probe'   => static function () {
			return 'lazyload threshold: default 300 -> ' . apply_filters( 'rocket_lazyload_threshold', 300 ) . 'px';
		},
	],
	'3. Vary cache by cookie (append + boolean combo)' => [
		'entries' => [
			[ 'rocket_cache_dynamic_cookies', 'rocket/append-to-list', [ 'values' => [ 'edd_items_in_cart' ] ] ],
			[ 'rocket_htaccess_mod_rewrite', 'core/return-false', [] ],
		],
		'probe'   => static function () {
			$cookies = apply_filters( 'rocket_cache_dynamic_cookies', [] );
			$rewrite = apply_filters( 'rocket_htaccess_mod_rewrite', '#RULES#' );
			return 'dynamic cookies = ' . wp_json_encode( $cookies )
				. ' | htaccess mod_rewrite = ' . var_export( $rewrite, true );
		},
	],
];

/**
 * Collects every filter name used across the demos.
 *
 * @return string[]
 */
$demo_filters = static function () use ( $demos ) {
	$names = [];
	foreach ( $demos as $demo ) {
		foreach ( $demo['entries'] as $entry ) {
			$names[ $entry[0] ] = true;
		}
	}
	return array_keys( $names );
};

/**
 * Registers the stored callbacks in this process so probes reflect them
 * (the applier normally runs on `init`, which has already fired here).
 */
$apply_now = static function () {
	( new MCPHelpers\FilterApplier(
		new MCPHelpers\Table\FilterCallbackTable(),
		new MCPHelpers\Callback\CallbackResolver( new MCPHelpers\Catalog\CallbackCatalog() ),
		new MCPHelpers\Catalog\FilterCatalog()
	) )->apply();
};

$table = new MCPHelpers\Table\FilterCallbackTable();

/**
 * Removes every stored entry that targets one of the demo filters.
 */
$remove_demo_rows = static function () use ( $table, $delete, $demo_filters ) {
	$targets = $demo_filters();
	$removed = 0;
	foreach ( $table->all() as $row ) {
		if ( in_array( $row['filter_name'], $targets, true ) ) {
			$delete->execute( [ 'id' => (int) $row['id'] ] );
			++$removed;
		}
	}
	return $removed;
};

$command = $argv[1] ?? 'status';

switch ( $command ) {
	case 'up':
		$remove_demo_rows(); // start clean / idempotent.
		foreach ( $demos as $title => $demo ) {
			echo "\n== {$title} ==\n";
			foreach ( $demo['entries'] as [ $filter, $callback, $args ] ) {
				$input  = [ 'filter_name' => $filter, 'callback_id' => $callback ];
				if ( $args ) {
					$input['args'] = $args;
				}
				$result = $create->execute( $input );
				if ( is_wp_error( $result ) ) {
					echo "  ✗ {$filter}: " . $result->get_error_message() . "\n";
					continue;
				}
				echo "  ✓ {$callback} on {$filter}\n";
			}
		}
		$apply_now();
		echo "\n-- live effect --\n";
		foreach ( $demos as $title => $demo ) {
			echo "  {$title}\n    " . $demo['probe']() . "\n";
		}
		echo "\nDone. These now apply on every front-end load.\n";
		break;

	case 'status':
		$apply_now();
		$rows = $read->execute( [] )['entries'];
		echo "Stored entries: " . count( $rows ) . "\n";
		foreach ( $rows as $row ) {
			echo "  #{$row['id']} {$row['filter_name']} -> " . wp_json_encode( $row['callback'] ) . "\n";
		}
		echo "\n-- live effect --\n";
		foreach ( $demos as $title => $demo ) {
			echo "  {$title}\n    " . $demo['probe']() . "\n";
		}
		break;

	case 'down':
		$removed = $remove_demo_rows();
		echo "Removed {$removed} demo entr" . ( 1 === $removed ? 'y' : 'ies' ) . ".\n";
		break;

	default:
		fwrite( STDERR, "Usage: php inc/MCP/tools/demo.php <up|status|down>\n" );
		exit( 1 );
}
