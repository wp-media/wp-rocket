<?php


/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function flush_rocket_launcher() {

	$boostrap = file_get_contents( WP_ROCKET_PATH . 'bootstrap-sample.php' );
	$boostrap = str_replace( '{{COOKIES_NOT_CACHED}}'		, get_rocket_cookies_not_cached(), $boostrap );
	$boostrap = str_replace( '{{WP_ROCKET_URL}}'			, WP_ROCKET_URL, $boostrap );
	$boostrap = str_replace( '{{WP_ROCKET_FRONT_PATH}}'		, WP_ROCKET_FRONT_PATH, $boostrap );
	$boostrap = str_replace( '{{WP_ROCKET_CACHE_URL}}'		, WP_ROCKET_CACHE_URL, $boostrap );
	$boostrap = str_replace( '{{CACHE_DIR}}'				, WP_ROCKET_CACHE_PATH, $boostrap );
	$boostrap = str_replace( '{{ABSPATH}}'					, ABSPATH, $boostrap );

	file_put_contents( WP_ROCKET_PATH . 'bootstrap.php', $boostrap   );

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function get_rocket_cookies_not_cached()
{

	$options = get_option( 'wp_rocket_settings' );

	$is_cache_not_logged_in  = $options['cache_not_logged_in'] == 1 ? true : false;
	$is_cache_comment_author = get_option( 'comment_moderation' ) == '1' || get_option( 'comment_whitelist' ) == '1' ? true : false;

	$cookies = array( 'wp-postpass_' );

	if( $is_cache_not_logged_in )
		$cookies[] = LOGGED_IN_COOKIE;

	if( $is_cache_comment_author )
		$cookies[] = 'comment_author_' . COOKIEHASH;

	return implode( '|', $cookies );

}



/**
 * Remove cache files
 *
 * @since 1.0
 *
 */
function rocket_clean_files( $urls )
{

	if( is_string( $urls ) )
		$urls = (array)$urls;

    foreach( (array)$urls as $url )
    {

		do_action( 'before_rocket_clean_file', $url );

		rocket_rrmdir( WP_ROCKET_CACHE_PATH .  str_replace( home_url( '/' ), '/', $url ) );

		do_action( 'after_rocket_clean_file', $url );

	}

}



/**
 * Remove all terms cache files of a specific post
 *
 * @since 1.0
 *
 */
function rocket_clean_post_terms( $post_ID )
{

	$urls = array();

	foreach ( get_object_taxonomies( get_post_type( $post_ID ) ) as $taxonomy )
	{

		// Get the terms related to post
		$terms = get_the_terms( $post_ID, $taxonomy );

		if ( !empty( $terms ) )
		{

			foreach ( $terms as $term )
				$urls[] = get_term_link( $term->slug, $taxonomy );

		}

	}

	do_action( 'before_rocket_clean_post_terms', $urls, $post_ID );

    rocket_clean_files( $urls );

    do_action( 'after_rocket_clean_post_terms', $urls, $post_ID );

}



/**
 *
 *
 * @since 1.0
 *
 */
function rocket_clean_post_dates( $post_ID )
{

	// Get the day and month of the post
	$date = explode( '-', get_the_time( 'Y-m-d', $post_ID ) );

	$urls = array(
		get_year_link( $date[0] ) . 'index.html.gz',
		get_year_link( $date[0] ) . $GLOBALS['wp_rewrite']->pagination_base,
		get_month_link( $date[0], $date[1] ),
		get_month_link( $date[0], $date[1] ) . $GLOBALS['wp_rewrite']->pagination_base,
		get_day_link( $date[0], $date[1], $date[2] ),
		get_day_link( $date[0], $date[1], $date[2] ) . $GLOBALS['wp_rewrite']->pagination_base
	);

	do_action( 'before_rocket_clean_post_dates', $urls, $post_ID );

	rocket_clean_files( $urls );

	do_action( 'after_rocket_clean_post_dates', $urls, $post_ID );


}



/**
 * Remove the home cache file and pagination
 *
 * @since 1.0
 *
 */
function rocket_clean_home()
{

	do_action( 'before_rocket_clean_home' );

    @unlink(  WP_ROCKET_CACHE_PATH . WPCM_CACHE_FILE );
    rrmdir(  WP_ROCKET_CACHE_PATH . '/' . $GLOBALS['wp_rewrite']->pagination_base );

    do_action( 'after_rocket_clean_home' );
}



/**
 * Remove all cache files of the domain
 *
 * @since 1.0
 *
 */

function rocket_clean_domain()
{

	do_action( 'before_rocket_clean_domain' );

    rocket_rrmdir( WP_ROCKET_CACHE_PATH );

    do_action( 'after_rocket_clean_domain' );

}



/**
 * Remove a single file or a folder recursively
 *
 * @since 1.0
 *
 */
function rocket_rrmdir( $dir )
{

	if( !is_dir( $dir ) ):
		@unlink( $dir );
		return;
	endif;

    foreach( glob( $dir . '/*' ) as $file )
        is_dir( $file ) ? rocket_rrmdir($file) : @unlink( $file );

    @rmdir($dir);

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */
function rocket_count_cache_contents( $base = null )
{
	$base = $base===null ? ( WP_ROCKET_CACHE_PATH ) : $base;

	$count = 0;

	if( !file_exists( $base ) )
		return $count;

	$root = scandir( $base );

    foreach( $root as $value )
    {
        if( $value=='.' || $value=='..' )
			continue;

        if( is_file( $base.'/'.$value ) )
			$count++;
		else
			$count = $count + rocket_count_cache_contents( $base.'/'.$value );
    }

    return $count;
}