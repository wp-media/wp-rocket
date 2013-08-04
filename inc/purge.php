<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

 // Launch hooks that deletes all the cache domain
add_action( 'switch_theme'				, 'rocket_clean_domain' );		// When user change theme
add_action( 'user_register'				, 'rocket_clean_domain' );		// When a user is added
add_action( 'profile_update'			, 'rocket_clean_domain' );		// When a user is updated
add_action( 'deleted_user'				, 'rocket_clean_domain' );		// When a user is deleted
add_action( 'wp_update_nav_menu'		, 'rocket_clean_domain' );		// When a custom menu is update
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'rocket_clean_domain' ); // When location af a menu is updated
add_action( 'update_option_sidebars_widgets', 'rocket_clean_domain' );	// When you change the order of widgets
add_action( 'update_option_category_base', 'rocket_clean_domain' );		// When category permalink prefix is update
add_action( 'update_option_tag_base'	, 'rocket_clean_domain' ); 		// When tag permalink prefix is update
add_action( 'permalink_structure_changed', 'rocket_clean_domain' ); 	// When permalink structure is update
add_action( 'edited_terms'				, 'rocket_clean_domain' ); 		// When a term is updated
add_action( 'delete_term'				, 'rocket_clean_domain' ); 		// When a term is deleted
add_action( 'add_link'					, 'rocket_clean_domain' );   	// When a link is added
add_action( 'edit_link'					, 'rocket_clean_domain' );		// When a link is updated
add_action( 'delete_link'				, 'rocket_clean_domain' );		// When a link is deleted

/* since 1.1.1 */
add_filter( 'widget_update_callback'	, 'rocket_widget_update_callback' ); // When a widget is update
function rocket_widget_update_callback( $instance ) { rocket_clean_domain(); return $instance; }



/**
 * Update cache when a post is updated or commented
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.3.0 Purge all parents of the post and the author page
 * @since 1.2.2 Add wp_trash_post and delete_post to purge cache when a post is trashed or deleted
 * @since 1.1.3 Use clean_post_cache instead of transition_post_status, transition_comment_status and preprocess_comment
 * @since 1.0
 *
 */

add_action( 'wp_trash_post', 'rocket_clean_post' );
add_action( 'delete_post', 'rocket_clean_post' );
add_action( 'clean_post_cache', 'rocket_clean_post' );
function rocket_clean_post( $post_id )
{
	// Get all post infos
	$post = get_post($post_id);
	
	// No purge for specifics conditions
	if( empty($post->post_type) || $post->post_type == 'nav_menu_item' )
		return;

	// Add permalink
	$purge_urls = array(
		get_permalink( $post_id ),
		get_post_type_archive_link( get_post_type( $post_id ) )
	);

	// Add next post
	$next_post = get_adjacent_post( false, '', false );
	if( $next_post )
		array_push( $purge_urls, get_permalink( $next_post ) );

	// Add next post in same category
	$next_in_same_cat_post = get_adjacent_post( true, '', false );
	if( $next_in_same_cat_post )
		array_push( $purge_urls, get_permalink( $next_in_same_cat_post ) );

	// Add previous post
	$previous_post = get_adjacent_post( false, '', true );
	if( $previous_post )
		array_push( $purge_urls, get_permalink( $previous_post ) );

	// Add previous post in same category
	$previous_in_same_cat_post = get_adjacent_post( true, '', true );
	if( $previous_in_same_cat_post )
		array_push( $purge_urls, get_permalink( $previous_in_same_cat_post ) );

	// Add urls page to purge every time a post is save
	$options = get_option( WP_ROCKET_SLUG );
	if( isset( $options['cache_purge_pages'] ) && count( $options['cache_purge_pages'] )>=1 )
		foreach( $options['cache_purge_pages'] as $page )
			array_push( $purge_urls, home_url( $page ) );

	// Add all terms archive page to purge
	$purge_terms = get_rocket_post_terms_urls( $post_id );
	if( count($purge_terms)>=1 )
		$purge_urls = array_merge( $purge_urls, $purge_terms );

	// Add all dates archive page to purge
	$purge_dates = get_rocket_post_dates_urls( $post_id );
	if( count($purge_dates)>=1 )
		$purge_urls = array_merge( $purge_urls, $purge_dates );
	
	// Add the author page
	$purge_author = array( get_author_posts_url( $post->post_author ) );
	$purge_urls = array_merge( $purge_urls, $purge_author );
	
	// Purge all files
	rocket_clean_files( apply_filters( 'rocket_post_purge_urls', $purge_urls ) );

	// Never forget to purge homepage and their pagination
	if( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') )
	{

		global $sitepress;
		$root = WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( $sitepress->language_url($sitepress->get_language_for_element($post_id, 'post_' . get_post_type($post_id))) );

		@unlink( $root . 'index.html' );
		rocket_rrmdir( $root . $GLOBALS['wp_rewrite']->pagination_base );

	}
	else {
		rocket_clean_home();
	}

	// Add Homepage URL to $purge_urls for bot crawl
	array_push( $purge_urls, home_url() );

	// Remove dates archives page and author page to preload cache
	$purge_urls = array_diff( $purge_urls , $purge_dates, $purge_author );
	//$purge_urls = array_diff( $purge_urls , $purge_author );

	// Create json file and run WP Rocket Bot
	$json_encode_urls = '["'.implode( '","', array_filter($purge_urls) ).'"]';
	if(@file_put_contents( WP_ROCKET_PATH . 'cache.json', $json_encode_urls ));
		run_rocket_bot( 'cache-json' );

	// Purge all parents
	$parents = get_post_ancestors( $post_id );
	if( count( $parents ) ) {

		foreach( $parents as $parent_id )
			rocket_clean_post( $parent_id);

	}

}



/**
 * Purge Cache file System in Admin Bar
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0
 *
 */

add_action( 'admin_post_purge_cache', 'rocket_purge_cache' );
function rocket_purge_cache()
{
	if( isset( $_GET['type'], $_GET['_wpnonce'] ) ) {

		$_type = explode( '-', $_GET['type'] );
		$_type = reset( $_type );
		$_id = explode( '-', $_GET['type'] );
		$_id = end( $_id );

		if( !wp_verify_nonce( $_GET['_wpnonce'], 'purge_cache_' . $_GET['type'] ) )
			wp_nonce_ays( '' );

		switch( $_type )
		{
			// Clear all cache domain
			case 'all':

				// Check if WPML is activated
				if( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {

					global $sitepress;

					$_lang = isset( $_GET['lang'] ) ? sanitize_key( $_GET['lang'] ) : 'all';
					// Get all active languages
					$langs = $sitepress->get_active_languages();

					// Check if user want to purge only one lang
					if( $_lang != 'all' ) {

						// Unset current lang to the preserve dirs
						unset($langs[$_lang]);

						// Assign a new array to stock dirs to preserve to the purge
						$langs_to_preserve = array();


						// Stock all URLs of langs to preserve
						foreach ( array_keys($langs) as $lang )
							$langs_to_preserve[] = rtrim(rocket_remove_url_protocol($sitepress->language_url($lang)),'/');


						// Remove only cache files of selected lang
						rocket_rrmdir(WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol($sitepress->language_url($_lang)), $langs_to_preserve);

					}
					else {
						// Remove all cache langs
						foreach ( array_keys($langs) as $lang )
							rocket_rrmdir(WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol($sitepress->language_url($lang)));

					}

				}
				else {
					// If WPML isn't activated, you can purge your domain normally
					rocket_clean_domain();
				}
				break;

			// Clear terms, homepage and other files associated at current post in back-end
			case 'post':
				rocket_clean_post( $_id );
				break;

			// Clear cache file of the current page in front-end
			case 'url':
				rocket_clean_files( wp_get_referer() );
				break;

			default:
				wp_nonce_ays( '' );
				break;
		}

		wp_redirect( wp_get_referer() );
		die();

	}

}



/**
 * Preload cache system in Admin Bar
 * It launch the WP Rocket Bot
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0 (delete in 1.1.6 and re-add in 1.1.9)
 *
 */

add_action( 'admin_post_preload', 'rocket_preload_cache' );
add_action( 'admin_post_nopriv_preload', 'rocket_preload_cache' );
function rocket_preload_cache()
{
    if( isset( $_GET['_wpnonce'] ) ) {

        if( !wp_verify_nonce( $_GET['_wpnonce'], 'preload' ) )
                wp_nonce_ays( '' );


		// Check if WPML is activated
		if( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {

			global $sitepress;
			$_lang = sanitize_key( $_GET['lang'] );

			// Check if user want to purge only one lang
			if( $_lang != 'all' ) {
				run_rocket_bot( 'cache-preload', $sitepress->language_url( $_lang ) );
			}
			else {

				// Get all active languages
				$langs = $sitepress->get_active_languages();
				foreach ( array_keys($langs) as $lang )
					run_rocket_bot( 'cache-preload',  $sitepress->language_url( $lang ) );

			}
		}
		else {
			run_rocket_bot( 'cache-preload' );
		}

        wp_redirect( wp_get_referer() );
        die();

    }
}