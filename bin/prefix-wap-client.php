<?php
/**
 * Post-Mozart step that makes the bundled groupone/wap-client library coexist with other copies of itself.
 *
 * Mozart only prefixes namespaced classes. This script renames the remaining global names (class,
 * functions, constants, AJAX actions and string class names) in the Mozart output, drops the plugin
 * activation hooks the library only needs when installed standalone, and asserts that nothing
 * unprefixed is left. It is idempotent: running it on already-processed files changes nothing.
 *
 * Usage: php bin/prefix-wap-client.php [--check] [path/to/inc/Dependencies/GroupOne/WapClient]
 * With --check nothing is written: only the post-conditions are asserted, and the exit code is non-zero on failure.
 *
 * @package WP_Rocket
 */

const WPR_WAP_NAMESPACE = 'WP_Rocket\\Dependencies\\GroupOne\\WapClient';
const WPR_WAP_CLASS     = 'WP_Rocket_WapClient';
const WPR_WAP_ACTIONS   = [ 'auth', 'delete_data', 'consent', 'column_state', 'permission_state' ];

/**
 * Stops the build with a message.
 *
 * @param string $message Reason.
 * @return void
 */
function wpr_wap_fail( $message ) {
	fwrite( STDERR, "prefix-wap-client: FAIL - {$message}\n" );
	exit( 1 );
}

/**
 * Asserts a condition.
 *
 * @param bool   $condition Condition that must hold.
 * @param string $message   Message when it does not.
 * @return void
 */
function wpr_wap_assert( $condition, $message ) {
	if ( ! $condition ) {
		wpr_wap_fail( $message );
	}
	echo "  ok: {$message}\n";
}

/**
 * Reads a file or fails.
 *
 * @param string $path File path.
 * @return string
 */
function wpr_wap_read( $path ) {
	$content = is_file( $path ) ? file_get_contents( $path ) : false;
	if ( false === $content ) {
		wpr_wap_fail( "cannot read {$path}" );
	}
	return $content;
}

/**
 * Counts regex matches.
 *
 * @param string $pattern Regex.
 * @param string $subject Text.
 * @return int
 */
function wpr_wap_count( $pattern, $subject ) {
	return (int) preg_match_all( $pattern, $subject );
}

$check_only = in_array( '--check', $argv, true );
$paths      = array_values( array_diff( array_slice( $argv, 1 ), [ '--check' ] ) );
$root       = isset( $paths[0] ) ? rtrim( $paths[0], '/' ) : dirname( __DIR__ ) . '/inc/Dependencies/GroupOne/WapClient';
if ( ! is_dir( $root ) ) {
	wpr_wap_fail( "{$root} not found, run mozart first" );
}

$main_path = $root . '/wap-client.php';
$api_path  = $root . '/wap-client-api.php';
$js_path   = $root . '/assets/wap-chat.js';
$includes  = glob( $root . '/includes/*.php' );
if ( empty( $includes ) ) {
	wpr_wap_fail( 'includes/*.php not found' );
}

$main = wpr_wap_read( $main_path );
$api  = wpr_wap_read( $api_path );
$js   = wpr_wap_read( $js_path );

$facade        = '/(?<![\w])WapClient::/';
$action_alt    = implode( '|', WPR_WAP_ACTIONS );
$quoted_action = '/\'wap_client_(' . $action_alt . ')\'/';
$quoted_to     = '\'wp_rocket_wap_client_$1\'';

if ( ! $check_only ) {
	// 1. The API file must still expose exactly the AJAX actions this script knows how to rename.
	$found = [];
	if ( preg_match_all( '/wp_ajax_(?:wp_rocket_)?wap_client_(\w+)\'/', $api, $m ) ) {
		$found = $m[1];
	}
	sort( $found );
	$expected = WPR_WAP_ACTIONS;
	sort( $expected );
	wpr_wap_assert( $found === $expected, 'library registers exactly the 5 known wp_ajax_wap_client_* actions' );

	// 2. Drop the standalone-plugin activation hooks and the capability grant they run.
	$main = preg_replace( '/^register_(?:de)?activation_hook\(__FILE__, \'(?:wp_rocket_)?wap_client_(?:de)?activate\'\);\n/m', '', $main );
	$main = preg_replace( '#// -+\n// Activation / deactivation hooks\n// -+\n\n#', '', $main );
	$api  = preg_replace( '#/\*\*\n \* On activation:.*?\nfunction (?:wp_rocket_)?wap_client_deactivate\(\): void \{.*?\n\}\n\n#s', '', $api );

	// 3. Global names: class, functions, constants.
	$api  = preg_replace( '/\bfinal class WapClient\b/', 'final class ' . WPR_WAP_CLASS, $api );
	$api  = preg_replace( '/(?<![\w\\\\])function wap_client_(load_textdomain|boot)\b/', 'function wp_rocket_wap_client_$1', $api );
	$api  = preg_replace( '/(?<![\w])wap_client_(load_textdomain|boot)\b/', 'wp_rocket_wap_client_$1', $api );
	$main = preg_replace( '/(?<![\w])WAP_CLIENT_(VERSION|FILE|DIR|URL)\b/', 'WP_ROCKET_WAP_CLIENT_$1', $main );
	$api  = preg_replace( '/(?<![\w])WAP_CLIENT_(VERSION|FILE|DIR|URL)\b/', 'WP_ROCKET_WAP_CLIENT_$1', $api );

	// 4. String class names (class map keys, callables): GroupOne\\WapClient\\X in PHP source.
	$string_fqcn = '/(?<![\w\\\\])GroupOne\\\\\\\\WapClient\\\\\\\\/';
	$string_to   = 'WP_Rocket\\\\\\\\Dependencies\\\\\\\\GroupOne\\\\\\\\WapClient\\\\\\\\';
	$main        = preg_replace( $string_fqcn, $string_to, $main );
	$api         = preg_replace( $string_fqcn, $string_to, $api );

	// 5. Facade references (calls and error-message labels) and AJAX action names.
	$main   = preg_replace( $facade, WPR_WAP_CLASS . '::', $main );
	$api    = preg_replace( $facade, WPR_WAP_CLASS . '::', $api );

	$api        = preg_replace( '/wp_ajax_wap_client_(' . $action_alt . ')\b/', 'wp_ajax_wp_rocket_wap_client_$1', $api );


	$js_before = wpr_wap_count( $quoted_action, $js );
	$js_after  = wpr_wap_count( '/\'wp_rocket_wap_client_(' . $action_alt . ')\'/', $js );
	wpr_wap_assert( 5 === $js_before + $js_after, 'wap-chat.js carries exactly 5 quoted AJAX action names' );
	$js = preg_replace( $quoted_action, $quoted_to, $js );

	$php_action_hits = 0;
	$contents        = [];
	foreach ( $includes as $file ) {
		$text = wpr_wap_read( $file );
		$text = preg_replace( $facade, WPR_WAP_CLASS . '::', $text );
		$text = preg_replace( '/(?<![\w])WAP_CLIENT_(VERSION|FILE|DIR|URL)\b/', 'WP_ROCKET_WAP_CLIENT_$1', $text );
		$text = preg_replace( $quoted_action, $quoted_to, $text );
		$php_action_hits += wpr_wap_count( '/\'wp_rocket_wap_client_(' . $action_alt . ')\'/', $text );
		$contents[ $file ] = $text;
	}
	wpr_wap_assert( 12 === $php_action_hits, 'includes/*.php carry 12 quoted AJAX action/nonce names (found ' . $php_action_hits . ')' );

	// 6. Write the results, drop the package files Composer no longer needs.
	file_put_contents( $main_path, $main );
	file_put_contents( $api_path, $api );
	file_put_contents( $js_path, $js );
	foreach ( $contents as $file => $text ) {
		file_put_contents( $file, $text );
	}
	foreach ( [ 'composer.json', 'README.md' ] as $leftover ) {
		if ( is_file( $root . '/' . $leftover ) ) {
			unlink( $root . '/' . $leftover );
		}
	}
}

// 7. Post-conditions, evaluated on what is on disk.
echo "Post-conditions:\n";
$all_php = array_merge( [ $main_path, $api_path ], $includes );
$php     = '';
foreach ( $all_php as $file ) {
	$php .= wpr_wap_read( $file ) . "\n";
}
$js = wpr_wap_read( $js_path );

wpr_wap_assert( 0 === wpr_wap_count( '/\bclass WapClient\b/', $php ), 'no unprefixed class WapClient' );
wpr_wap_assert( 1 === wpr_wap_count( '/^final class ' . WPR_WAP_CLASS . '\b/m', $php ), 'exactly one final class ' . WPR_WAP_CLASS );
wpr_wap_assert( 0 === wpr_wap_count( '/\bfunction wap_client_/', $php ), 'no unprefixed function wap_client_*' );
preg_match_all( '/^function (\w+)\(/m', $php, $fn );
sort( $fn[1] );
wpr_wap_assert( $fn[1] === [ 'wp_rocket_wap_client_boot', 'wp_rocket_wap_client_load_textdomain' ], 'only 2 global functions remain, both prefixed' );
wpr_wap_assert( 0 === wpr_wap_count( '/(?<![\w])WAP_CLIENT_(VERSION|FILE|DIR|URL)\b/', $php ), 'no unprefixed WAP_CLIENT_{VERSION,FILE,DIR,URL}' );
wpr_wap_assert( 4 === wpr_wap_count( '/define\(\'WP_ROCKET_WAP_CLIENT_(VERSION|FILE|DIR|URL)\'/', $php ), '4 WP_ROCKET_WAP_CLIENT_* constants defined' );
wpr_wap_assert( 0 === wpr_wap_count( '/(?<![\w\\\\])GroupOne\\\\\\\\WapClient/', $php ), 'no unprefixed GroupOne\\\\WapClient string literal' );
wpr_wap_assert( 17 === wpr_wap_count( '/\'' . preg_quote( 'WP_Rocket\\\\Dependencies\\\\GroupOne\\\\WapClient\\\\', '/' ) . '\w+\'\s+=>/', $php ), '17 class map keys prefixed' );
wpr_wap_assert( 6 === wpr_wap_count( '/\[\'' . preg_quote( 'WP_Rocket\\\\Dependencies\\\\GroupOne\\\\WapClient\\\\', '/' ) . '\w+\', \'/', $php ), '6 callables prefixed' );
wpr_wap_assert( 0 === wpr_wap_count( '/(?<![\w])\\\\?WapClient::/', $php ), 'no unprefixed WapClient:: reference' );
wpr_wap_assert( 5 === wpr_wap_count( '/wp_ajax_wp_rocket_wap_client_(' . $action_alt . ')\'/', $php ), '5 wp_ajax_wp_rocket_wap_client_* hooks' );
wpr_wap_assert( 0 === wpr_wap_count( '/wp_ajax_wap_client_/', $php ), 'no unprefixed wp_ajax_wap_client_* hook' );
wpr_wap_assert( 0 === wpr_wap_count( $quoted_action, $php ), 'no unprefixed quoted action/nonce name in PHP' );
wpr_wap_assert( 0 === wpr_wap_count( $quoted_action, $js ), 'no unprefixed quoted action name in JS' );
wpr_wap_assert( 5 === wpr_wap_count( '/\'wp_rocket_wap_client_(' . $action_alt . ')\'/', $js ), 'JS has 5 prefixed action names' );
wpr_wap_assert( 1 === wpr_wap_count( '/\'wap_client_consent_required\'/', $js ), 'JS keeps wap_client_consent_required untouched' );
wpr_wap_assert( 0 === wpr_wap_count( '/register_(?:de)?activation_hook|wap_client_(?:de)?activate/', $php ), 'no activation/deactivation hooks or handlers' );
wpr_wap_assert( ! is_file( $root . '/composer.json' ) && ! is_file( $root . '/README.md' ), 'composer.json and README.md removed' );

foreach ( $all_php as $file ) {
	$out  = [];
	$code = 0;
	exec( escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $file ) . ' 2>&1', $out, $code );
	if ( 0 !== $code ) {
		wpr_wap_fail( 'php -l failed for ' . $file . ': ' . implode( ' ', $out ) );
	}
}
wpr_wap_assert( true, 'php -l passes on ' . count( $all_php ) . ' files' );
echo "prefix-wap-client: done\n";
