<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Get all terms archives urls associated to a specific post
 *
 * @since 1.0
 *
 * @param int $post_ID The post ID
 * @return array $urls List of taxonomies URLs
 */
function get_rocket_post_terms_urls( $post_ID )
{
	$urls = array();
	$taxonomies = get_object_taxonomies( get_post_type( $post_ID ), 'objects' );
	
	foreach( $taxonomies as $taxonomy ) {
		if( ! $taxonomy->public ) {
			continue;
		}
		
		// Get the terms related to post
		$terms = get_the_terms( $post_ID, $taxonomy->name );
		
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_url = get_term_link( $term->slug, $taxonomy->name );
				
				if ( ! is_wp_error( $term_url ) ) {
					$urls[] = $term_url;	
				}
			}
		}
	}
	
	/**
	 * Filter the list of taxonomies URLs
	 *
	 * @since 1.1.0
	 *
	 * @param array $urls List of taxonomies URLs
	*/
	$urls = apply_filters( 'rocket_post_terms_urls', $urls );

	return $urls;
}

/**
 * Get all dates archives urls associated to a specific post
 *
 * @since 1.0
 *
 * @param int $post_ID The post ID
 * @return array $urls List of dates URLs
 */
function get_rocket_post_dates_urls( $post_ID )
{
	// Get the day and month of the post
	$date = explode( '-', get_the_time( 'Y-m-d', $post_ID ) );

	$urls = array(
		trailingslashit( get_year_link ( $date[0] ) ) . 'index.html',
		trailingslashit( get_year_link ( $date[0] ) ) . $GLOBALS['wp_rewrite']->pagination_base,
		trailingslashit( get_month_link( $date[0], $date[1] ) ) . 'index.html',
		trailingslashit( get_month_link( $date[0], $date[1] ) ) . $GLOBALS['wp_rewrite']->pagination_base,
		get_day_link  ( $date[0], $date[1], $date[2] )
	);
	
	/**
	 * Filter the list of dates URLs
	 *
	 * @since 1.1.0
	 *
	 * @param array $urls List of dates URLs
	*/
	$urls = apply_filters( 'rocket_post_dates_urls', $urls );
	
    return $urls;
}

/**
 * Get the permalink post
 *
 * @since 1.3.1
 *
 * @source : get_sample_permalink() in wp-admin/includes/post.php
 */
function get_rocket_sample_permalink($id, $title = null, $name = null)
{
	$post = get_post( $id );
	if ( ! $post )
		return array( '', '' );

	$ptype = get_post_type_object($post->post_type);

	$original_status = $post->post_status;
	$original_date = $post->post_date;
	$original_name = $post->post_name;

	// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
	if ( in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
		$post->post_status = 'publish';
		$post->post_name = sanitize_title($post->post_name ? $post->post_name : $post->post_title, $post->ID);
	}

	// If the user wants to set a new name -- override the current one
	// Note: if empty name is supplied -- use the title instead, see #6072
	if ( !is_null($name) )
		$post->post_name = sanitize_title($name ? $name : $title, $post->ID);

	$post->post_name = wp_unique_post_slug($post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent);

	$post->filter = 'sample';

	$permalink = get_permalink($post, false);

	// Replace custom post_type Token with generic pagename token for ease of use.
	$permalink = str_replace("%$post->post_type%", '%pagename%', $permalink);

	// Handle page hierarchy
	if ( $ptype->hierarchical ) {
		$uri = get_page_uri($post);
		$uri = untrailingslashit($uri);
		$uri = strrev( stristr( strrev( $uri ), '/' ) );
		$uri = untrailingslashit($uri);

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$uri = apply_filters( 'editable_slug', $uri );
		if ( !empty($uri) )
			$uri .= '/';
		$permalink = str_replace('%pagename%', "{$uri}%pagename%", $permalink);
	}

	/** This filter is documented in wp-admin/edit-tag-form.php */
	$permalink = array( $permalink, apply_filters( 'editable_slug', $post->post_name ) );
	$post->post_status = $original_status;
	$post->post_date = $original_date;
	$post->post_name = $original_name;
	unset($post->filter);

	return $permalink;
}