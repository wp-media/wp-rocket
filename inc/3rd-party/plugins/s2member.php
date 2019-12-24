<?php

defined( 'ABSPATH' ) || exit;

/**
 * Excludes s2 Member dynamic file from saving as static resource
 *
 * @since 2.11.4
 * @author Remy Perona
 *
 * @param array $excluded_files Array of excluded files.
 */
function rocket_exclude_s2member_dynamic_files( $excluded_files ) {
	$excluded_files[] = rocket_clean_exclude_file( plugins_url( '/s2member/s2member-o.php' ) );

	return $excluded_files;
}
add_action( 'rocket_exclude_static_dynamic_resources', 'rocket_exclude_s2member_dynamic_files' );
