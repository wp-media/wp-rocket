<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\Engine\Optimization\ServiceProvider', '\WP_Rocket\ServiceProvider\Optimization_Subscribers' );

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
