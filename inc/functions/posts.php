<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get the permalink post
 *
 * @since 1.3.1
 *
 * @source : get_sample_permalink() in wp-admin/includes/post.php
 *
 * @param int    $id The post ID.
 * @param string $title The post title.
 * @param string $name The post name.
 * @return string The permalink
 */
function get_rocket_sample_permalink( $id, $title = null, $name = null ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$post = get_post( $id );
	if ( ! $post ) {
		return [ '', '' ];
	}

	$ptype = get_post_type_object( $post->post_type );

	$original_status = $post->post_status;
	$original_date   = $post->post_date;
	$original_name   = $post->post_name;

	// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
	if ( in_array( $post->post_status, [ 'draft', 'pending' ], true ) ) {
		$post->post_status = 'publish';
		$post->post_name   = sanitize_title( $post->post_name ? $post->post_name : $post->post_title, $post->ID );
	}

	// If the user wants to set a new name -- override the current one.
	// Note: if empty name is supplied -- use the title instead, see #6072.
	if ( ! is_null( $name ) ) {
		$post->post_name = sanitize_title( $name ? $name : $title, $post->ID );
	}

	$post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent );

	$post->filter = 'sample';

	$permalink = get_permalink( $post, false );

	// Replace custom post_type Token with generic pagename token for ease of use.
	$permalink = str_replace( "%$post->post_type%", '%pagename%', $permalink );

	// Handle page hierarchy.
	if ( $ptype->hierarchical ) {
		$uri = get_page_uri( $post );
		$uri = untrailingslashit( $uri );
		$uri = strrev( stristr( strrev( $uri ), '/' ) );
		$uri = untrailingslashit( $uri );

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$uri = apply_filters( 'editable_slug', $uri, $post ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		if ( ! empty( $uri ) ) {
			$uri .= '/';
		}
		$permalink = str_replace( '%pagename%', "{$uri}%pagename%", $permalink );
	}

	/** This filter is documented in wp-admin/edit-tag-form.php */
	$permalink         = [ $permalink, apply_filters( 'editable_slug', $post->post_name, $post ) ]; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$post->post_status = $original_status;
	$post->post_date   = $original_date;
	$post->post_name   = $original_name;
	unset( $post->filter );

	return $permalink;
}
