<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

// Launch hooks that deletes all the cache domain
add_action( 'switch_theme', 'rocket_clean_domain' );					// When user change theme
add_action( 'wp_update_nav_menu', 'rocket_clean_domain' );				// When a custom menu is update
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'rocket_clean_domain' );
add_action( 'update_option_sidebars_widgets', 'rocket_clean_domain' );	// When you change the order of widgets
add_action( 'update_option_category_base', 'rocket_clean_domain' );		// When category permalink prefix is update
add_action( 'update_option_tag_base', 'rocket_clean_domain' ); 			// When tag permalink prefix is update
add_action( 'permalink_structure_changed', 'rocket_clean_domain' ); 	// When permalink structure is update
add_filter( 'widget_update_callback', 'rocket_clean_domain' ); 			// When a widget is update
add_filter( 'edited_terms', 'rocket_clean_domain' ); 					// When a term is updated
add_filter( 'delete_term', 'rocket_clean_domain' ); 					// When a term is deleted



/**
 * Update cache at every save of a publish post
 *
 * @since 1.0
 *
 */

add_action( 'transition_post_status', 'rocket_clean_post', 10, 3 );
function rocket_clean_post( $new_status, $old_status, $post )
{
    if( $new_status == 'publish' || $old_status == 'publish' ) {

		$purge_urls = array(
			get_permalink( $post->ID ),
			get_post_type_archive_link( $post->post_type ),
			get_permalink( get_adjacent_post( false,'', false ) ),
			get_permalink( get_adjacent_post( true,'', false ) ),
			get_permalink( get_adjacent_post( false, '', true ) ),
			get_permalink( get_adjacent_post( true, '', true ) )
		);
		
		// Add urls page to purge every time a post is save
		$options = get_option( WP_ROCKET_SLUG );
		if( isset( $options['cache_purge_pages'] ) && count( $options['cache_purge_pages'] )>=1 ) 
			foreach( $options['cache_purge_pages'] as $page )
				array_push( $purge_urls, home_url( $page ) );
		
		// Add all terms archive page to purge
		$purge_terms = get_rocket_post_terms_urls( $post->ID );
		if( count($purge_terms)>=1 )
			$purge_urls = array_merge( $purge_urls, $purge_terms );

		// Add all dates archive page to purge
		$purge_dates = get_rocket_post_dates_urls( $post->ID );
		if( count($purge_dates)>=1 )
			$purge_urls = array_merge( $purge_urls, $purge_dates );

		// Purge all files
		rocket_clean_files( apply_filters( 'rocket_post_purge_urls', $purge_urls ) );

		// Never forget to purge homepage and their pagination
		rocket_clean_home();

    }

}



/**
 * Update cache on comment add and update
 *
 * @since 1.0
 *
 */

add_action( 'transition_comment_status','rocket_clean_comment', 10, 3  );
add_action( 'preprocess_comment', 'rocket_clean_comment' );
function rocket_clean_comment( $arg1, $arg2 = '', $arg3 = '' )
{

    $post_ID = current_filter() == 'preprocess_comment' ? $arg1['comment_post_ID'] : $arg3->comment_post_ID;
    $post_type = get_post_type( $post_ID );

	$purge_urls = array(
		get_permalink( $post_ID ),
		get_post_type_archive_link( $post_type )
	);
	
	// Add urls page to purge every time a post is save
	$options = get_option( WP_ROCKET_SLUG );
	if( isset( $options['cache_purge_pages'] ) && count( $options['cache_purge_pages'] )>=1 ) 
		foreach( $options['cache_purge_pages'] as $page )
			array_push( $purge_urls, home_url( $page ) );
	
	// Add all terms archive page to purge
	$purge_terms = get_rocket_post_terms_urls( $post_ID );
	if( count($purge_terms)>=1 )
		$purge_urls = array_merge( $purge_urls, $purge_terms );

	// Add all dates archive page to purge
	$purge_dates = get_rocket_post_dates_urls( $post_ID );
	if( count($purge_dates)>=1 )
		$purge_urls = array_merge( $purge_urls, $purge_dates );

	// Purge all files
	rocket_clean_files( apply_filters( 'rocket_comment_purge_urls', $purge_urls ) );

	// Never forget to purge homepage and their pagination
	rocket_clean_home();

    // Return data for preprocess_comment filter
    if( current_filter() == 'preprocess_comment' )
		return $arg1;
}



/**
 * TO DO - Description
 *
 * @since 1.0
 *
 */

add_action( 'admin_post_purge_cache', 'rocket_purge_cache' );
function rocket_purge_cache()
{
	if( isset( $_GET['type'], $_GET['_wpnonce'] ) ) {

		$_type = explode( '-', $_GET['type'] );
		$_type = reset( $_type );
		$_id = end( explode( '-', $_GET['type'] ) );

		if( !wp_verify_nonce( $_GET['_wpnonce'], 'purge_cache_' . $_GET['type'] ) )
			defined( 'DOING_AJAX' ) && DOING_AJAX ? die( '-1' ) : wp_nonce_ays( '' );

		switch( $_type )
		{

			case 'all':
				rocket_clean_domain();
				break;

			case 'post':
				rocket_clean_post( 'publish', '', get_post( $_id ) );
				break;

			case 'url':
				$p = get_post( url_to_postid( wp_get_referer() ) );
				if( $p )
					rocket_clean_post( 'publish', '', $p );
					break;

			default:
				wp_nonce_ays( '' );
				break;
		}

		if( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
			wp_redirect( wp_get_referer() );
			die();
		}

	}

}



/**
 * TO DO - Description
 *
 * @since 1.0
 *
 */

add_action( 'admin_post_preload', 'rocket_preload_cache' );
add_action( 'admin_post_nopriv_preload', 'rocket_preload_cache' );
function rocket_preload_cache()
{
	if( isset( $_GET['_wpnonce'] ) ) {

		if( !wp_verify_nonce( $_GET['_wpnonce'], 'preload' ) )
			defined( 'DOING_AJAX' ) && DOING_AJAX ? die( '-1' ) : wp_nonce_ays( '' );

		wp_remote_get( 'http://bot.wp-rocket.me/launch.php?&spider=cache-preload&start_url=' . home_url() . '&allow_url=' . rocket_get_domain( home_url() ) );

		if( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
			wp_redirect( wp_get_referer() );
			die();
		}

	}

}