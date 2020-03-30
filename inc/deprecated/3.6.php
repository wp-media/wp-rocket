<?php
/**
 * Exclude fusion styles from cache busting to prevent cache dir issues
 *
 * @deprecated 3.6
 * @author Remy Perona
 *
 * @param array $excluded_files An array of excluded files.
 * @return array
 */
function rocket_exclude_avada_dynamic_css( $excluded_files ) {
    _deprecated_function( __FUNCTION__ . '()', '3.6' );

    $upload_dir = wp_upload_dir();

    $excluded_files[] = rocket_clean_exclude_file( $upload_dir['baseurl'] . '/fusion-styles/(.*)' );

    return $excluded_files;
}

/**
 * Excludes Uncode JS files from remove query strings
 *
 * @deprecated 3.6
 * @since 3.3.3
 * @author Remy Perona
 *
 * @param array $exclude_busting Array of CSS and JS filepaths to be excluded.
 * @return array
 */
function rocket_exclude_busting_uncode( $exclude_busting ) {
    _deprecated_function( __FUNCTION__ . '()', '3.6' );

    // CSS files.
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/style.css' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/uncode-icons.css' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/css/style-custom.css' );

    // JS files.
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/init.min.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/app.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/app.min.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/plugins.js' );
    $exclude_busting[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/plugins.min.js' );
    return $exclude_busting;
}

/**
 * Purge the cache when the beaver builder layout is updated to update the minified files content & URL
 *
 * @deprecated 3.6
 * @since 2.9 Also clear the cache busting folder
 * @since 2.8.6
 */
function rocket_beaver_builder_clean_domain() {
    _deprecated_function( __FUNCTION__ . '()', '3.6', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache' );
	rocket_clean_minify();
	rocket_clean_domain();
}

/**
 * This warning is displayed when the busting cache dir isn't writeable
 *
 * @since 2.9
 * @deprecated 3.6
 * @author Remy Perona
 */
function rocket_warning_busting_cache_dir_permissions() {
	_deprecated_function( __FUNCTION__ . '()', '3.6' );
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_BUSTING_PATH ) )
		&& ( get_rocket_option( 'remove_query_strings', false ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_BUSTING_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
