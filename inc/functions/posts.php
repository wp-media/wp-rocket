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
 *
 * @return array
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

if ( ! function_exists( 'rocket_url_to_postid' ) ) {
	/**
	 * Get the post ID from the URL.
	 *
	 * @param string         $url URL of the page.
	 * @param array|string[] $search_in_post_statuses Post statuses to search in.
	 * @return float|int Post ID.
	 */
	function rocket_url_to_postid( string $url, array $search_in_post_statuses = [ 'publish', 'private' ] ) {
		global $wp_rewrite;

		/**
		 * Filters the URL to derive the post ID from.
		 *
		 * @since 2.2.0
		 *
		 * @param string $url The URL to derive the post ID from.
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$url = apply_filters( 'url_to_postid', $url );

		$url_host = wp_parse_url( $url, PHP_URL_HOST );

		if ( is_string( $url_host ) ) {
			$url_host = str_replace( 'www.', '', $url_host );
		} else {
			$url_host = '';
		}

		$home_url_host = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( is_string( $home_url_host ) ) {
			$home_url_host = str_replace( 'www.', '', $home_url_host );
		} else {
			$home_url_host = '';
		}

		// Bail early if the URL does not belong to this site.
		if ( $url_host && $url_host !== $home_url_host ) {
			return 0;
		}

		// First, check to see if there is a 'p=N' or 'page_id=N' to match against.
		if ( preg_match( '#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values ) ) {
			$id = absint( $values[2] );
			if ( $id ) {
				if ( empty( $search_in_post_statuses ) || ! in_array( get_post_status( $id ), $search_in_post_statuses, true ) ) {
					return 0;
				}
				return $id;
			}
		}

		// Get rid of the #anchor.
		$url_split = explode( '#', $url );
		$url       = $url_split[0];

		// Get rid of URL ?query=string.
		$url_split = explode( '?', $url );
		$url       = $url_split[0];

		// Set the correct URL scheme.
		$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
		$url    = set_url_scheme( $url, $scheme );

		// Add 'www.' if it is absent and should be there.
		if ( str_contains( home_url(), '://www.' ) && ! str_contains( $url, '://www.' ) ) {
			$url = str_replace( '://', '://www.', $url );
		}

		// Strip 'www.' if it is present and shouldn't be.
		if ( ! str_contains( home_url(), '://www.' ) ) {
			$url = str_replace( '://www.', '://', $url );
		}

		if ( trim( $url, '/' ) === home_url() && 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front = get_option( 'page_on_front' );

			if ( $page_on_front && get_post( $page_on_front ) instanceof WP_Post ) {
				if ( empty( $search_in_post_statuses ) || ! in_array( get_post_status( (int) $page_on_front ), $search_in_post_statuses, true ) ) {
					return 0;
				}
				return (int) $page_on_front;
			}
		}

		// Check to see if we are using rewrite rules.
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options.
		if ( empty( $rewrite ) ) {
			return 0;
		}

		// Strip 'index.php/' if we're not using path info permalinks.
		if ( ! $wp_rewrite->using_index_permalinks() ) {
			$url = str_replace( $wp_rewrite->index . '/', '', $url );
		}

		if ( str_contains( trailingslashit( $url ), home_url( '/' ) ) ) {
			// Chop off http://domain.com/[path].
			$url = str_replace( home_url(), '', $url );
		} else {
			// Chop off /path/to/blog.
			$home_path = wp_parse_url( home_url( '/' ) );
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '';
			$url       = preg_replace( sprintf( '#^%s#', preg_quote( $home_path, '#' ) ), '', trailingslashit( $url ) );
		}

		// Trim leading and lagging slashes.
		$url = trim( $url, '/' );

		$request              = $url;
		$post_type_query_vars = [];

		foreach ( get_post_types( [], 'objects' ) as $post_type => $t ) {
			if ( ! empty( $t->query_var ) ) {
				$post_type_query_vars[ $t->query_var ] = $post_type;
			}
		}

		// Look for matches.
		$request_match = $request;
		foreach ( (array) $rewrite as $match => $query ) {
			/*
			 * If the requesting file is the anchor of the match,
			 * prepend it to the path info.
			 */
			if ( ! empty( $url ) && ( $url !== $request ) && str_starts_with( $match, $url ) ) {
				$request_match = $url . '/' . $request;
			}

			if ( preg_match( "#^$match#", $request_match, $matches ) ) {

				if ( $wp_rewrite->use_verbose_page_rules && preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query, $varmatch ) ) {
					// This is a verbose page match, let's check to be sure about it.
					$page = get_page_by_path( $matches[ $varmatch[1] ] );
					if ( ! $page ) {
						continue;
					}

					$post_status_obj = get_post_status_object( $page->post_status );
					if ( ! $post_status_obj->public && ! $post_status_obj->protected
						&& ! $post_status_obj->private && $post_status_obj->exclude_from_search ) {
						continue;
					}
				}

				/*
				 * Got a match.
				 * Trim the query of everything up to the '?'.
				 */
				$query = preg_replace( '!^.+\?!', '', $query );

				// Substitute the substring matches into the query.
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );

				// Filter out non-public query vars.
				global $wp;
				parse_str( $query, $query_vars );
				$query = [];
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array( (string) $key, $wp->public_query_vars, true ) ) {
						$query[ $key ] = $value;
						if ( isset( $post_type_query_vars[ $key ] ) ) {
							$query['post_type'] = $post_type_query_vars[ $key ];
							$query['name']      = $value;
						}
					}
				}

				// Resolve conflicts between posts with numeric slugs and date archive queries.
				$query = wp_resolve_numeric_slug_conflicts( $query );

				if ( ! empty( $search_in_post_statuses ) ) {
					$query['post_status'] = $search_in_post_statuses;
				}

				/**
				 * Filters WP_Query class passed args.
				 *
				 * @param array $query WP_Query passed args.
				 * @param string $url The URL to derive the post ID from.
				 */
				$query = (array) apply_filters( 'rocket_url_to_postid_query_args', $query, $url );

				$query['no_found_rows']          = true;
				$query['update_post_term_cache'] = false;
				$query['update_post_meta_cache'] = false;

				// Do the query.
				$query = new WP_Query( $query );

				if ( ! empty( $query->posts ) && $query->is_singular ) {
					return $query->post->ID;
				} else {
					return 0;
				}
			}
		}
		return 0;
	}
}
