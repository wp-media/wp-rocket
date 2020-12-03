<?php

defined( 'ABSPATH' ) || exit;

// Launch hooks that deletes all the cache domain.
add_action( 'switch_theme', 'rocket_clean_domain' );  // When user change theme.
add_action( 'wp_update_nav_menu', 'rocket_clean_domain' );  // When a custom menu is update.
add_action( 'update_option_sidebars_widgets', 'rocket_clean_domain' );  // When you change the order of widgets.
add_action( 'update_option_category_base', 'rocket_clean_domain' );  // When category permalink is updated.
add_action( 'update_option_tag_base', 'rocket_clean_domain' );  // When tag permalink is updated.
add_action( 'permalink_structure_changed', 'rocket_clean_domain' );  // When permalink structure is update.
add_action( 'add_link', 'rocket_clean_domain' );  // When a link is added.
add_action( 'edit_link', 'rocket_clean_domain' );  // When a link is updated.
add_action( 'delete_link', 'rocket_clean_domain' );  // When a link is deleted.
add_action( 'customize_save', 'rocket_clean_domain' );  // When customizer is saved.
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'rocket_clean_domain' ); // When location of a menu is updated.

/**
 * Purge cache When a widget is updated.
 *
 * @since 1.1.1
 *
 * @param object $instance Widget instance.
 * @return object Widget instance.
 */
function rocket_widget_update_callback( $instance ) {
	rocket_clean_domain();
	return $instance;
}
add_filter( 'widget_update_callback', 'rocket_widget_update_callback' );

/**
 * Get post purge urls.
 *
 * @since 3.4.3
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    WP_Post object.
 * @return array           Array with all URLs which need to be purged.
 */
function rocket_get_purge_urls( $post_id, $post ) {
	$purge_urls = [];

	// Get the permalink structure.
	$permalink_structure = get_rocket_sample_permalink( $post_id );

	// Get permalink.
	$permalink = str_replace( [ '%postname%', '%pagename%' ], $permalink_structure[1], $permalink_structure[0] );

	// Add permalink.
	if ( rocket_extract_url_component( $permalink, PHP_URL_PATH ) !== '/' ) {
		$purge_urls[] = $permalink;
	}

	// Add Posts page.
	if ( 'post' === $post->post_type && (int) get_option( 'page_for_posts' ) > 0 ) {
		$purge_urls[] = get_permalink( get_option( 'page_for_posts' ) );
	}

	// Add Post Type archive.
	$post_type = $post->post_type;
	if ( 'post' !== $post_type ) {
		$post_type_archive = get_post_type_archive_link( $post_type );
		if ( $post_type_archive ) {
			// Rename the caching filename for SSL URLs.
			$filename = 'index';
			if ( is_ssl() ) {
				$filename .= '-https';
			}

			$post_type_archive = trailingslashit( $post_type_archive );
			$purge_urls[]      = $post_type_archive . $filename . '.html';
			$purge_urls[]      = $post_type_archive . $filename . '.html_gzip';
			$purge_urls[]      = $post_type_archive . $filename . $GLOBALS['wp_rewrite']->pagination_base;
		}
	}

	// Add next post.
	$next_post = get_adjacent_post( false, '', false );
	if ( $next_post ) {
		$purge_urls[] = get_permalink( $next_post );
	}

	// Add next post in same category.
	$next_in_same_cat_post = get_adjacent_post( true, '', false );
	if ( $next_in_same_cat_post && $next_in_same_cat_post !== $next_post ) {
		$purge_urls[] = get_permalink( $next_in_same_cat_post );
	}

	// Add previous post.
	$previous_post = get_adjacent_post( false, '', true );
	if ( $previous_post ) {
		$purge_urls[] = get_permalink( $previous_post );
	}

	// Add previous post in same category.
	$previous_in_same_cat_post = get_adjacent_post( true, '', true );
	if ( $previous_in_same_cat_post && $previous_in_same_cat_post !== $previous_post ) {
		$purge_urls[] = get_permalink( $previous_in_same_cat_post );
	}

	// Add urls page to purge every time a post is save.
	$cache_purge_pages = get_rocket_option( 'cache_purge_pages' );
	if ( $cache_purge_pages ) {
		global $blog_id;

		$home_url = get_option( 'home' );

		if ( ! empty( $blog_id ) && is_multisite() ) {
			switch_to_blog( $blog_id );
			$home_url = get_option( 'home' );
			restore_current_blog();
		}

		$home_parts = get_rocket_parse_url( $home_url );
		$home_url   = "{$home_parts['scheme']}://{$home_parts['host']}";
		$cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $home_parts['host'];

		foreach ( $cache_purge_pages as $page ) {
			// Check if it contains regex pattern.
			if ( strstr( $page, '*' ) ) {
				$matches_files = _rocket_get_recursive_dir_files_by_regex( '#' . $page . '#i' );
				foreach ( $matches_files as $file ) {
					// Convert path to URL.
					$purge_urls[] = str_replace( $cache_path, untrailingslashit( $home_url ), $file->getPath() );
				}
				continue;
			}
			$purge_urls[] = trailingslashit( $home_url ) . $page;
		}
	}

	// Add the author page.
	$purge_urls[] = get_author_posts_url( $post->post_author );

	// Add all parents.
	$parents = get_post_ancestors( $post_id );
	if ( (bool) $parents ) {
		foreach ( $parents as $parent_id ) {
			$purge_urls[] = get_permalink( $parent_id );
		}
	}

	return array_flip( array_flip( $purge_urls ) );
}

/**
 * Update cache when a post is updated or commented
 *
 * @since 3.0.5 Don't purge for attachment post type
 * @since 2.8   Only add post type archive if post type is not post
 * @since 2.6   Purge the page defined in "Posts page" option
 * @since 2.5.5 Don't cache for auto-draft post status
 * @since 1.3.2 Add wp_update_comment_count to purge cache when a comment is added/updated/deleted
 * @since 1.3.0 Compatibility with WPML
 * @since 1.3.0 Add 2 hooks : before_rocket_clean_post, after_rocket_clean_post
 * @since 1.3.0 Purge all parents of the post and the author page
 * @since 1.2.2 Add wp_trash_post and delete_post to purge cache when a post is trashed or deleted
 * @since 1.1.3 Use clean_post_cache instead of transition_post_status, transition_comment_status and preprocess_comment
 * @since 1.0
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    WP_Post object.
 */
function rocket_clean_post( $post_id, $post = null ) {
	static $done = [];

	if ( isset( $done[ $post_id ] ) ) {
		return false;
	}

	$done[ $post_id ] = 1;

	if ( defined( 'DOING_AUTOSAVE' ) ) {
		return false;
	}

	$purge_urls = [];

	// Get all post infos if the $post object was not supplied.
	if ( is_null( $post ) ) {
		$post = get_post( $post_id );
	}

	// Return if $post is not an object.
	if ( ! is_object( $post ) ) {
		return false;
	}

	// No purge for specific conditions.
	if ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status || empty( $post->post_type ) || 'nav_menu_item' === $post->post_type || 'attachment' === $post->post_type ) {
		return false;
	}

	// Don't purge if post's post type is not public or not publicly queryable.
	$post_type = get_post_type_object( $post->post_type );
	if ( ! is_object( $post_type ) || true !== $post_type->public ) {
		return false;
	}

	// Get the post language.
	$i18n_plugin = rocket_has_i18n();
	$lang        = false;

	if ( 'wpml' === $i18n_plugin && ! rocket_is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
		// WPML.
		$lang = $GLOBALS['sitepress']->get_language_for_element( $post_id, 'post_' . get_post_type( $post_id ) );
	} elseif ( 'polylang' === $i18n_plugin && function_exists( 'pll_get_post_language' ) ) {
		// Polylang.
		$lang = pll_get_post_language( $post_id );
	}

	$purge_urls = rocket_get_purge_urls( $post_id, $post );

	/**
	 * Fires before cache files related with the post are deleted
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post       The post object
	 * @param array   $purge_urls URLs cache files to remove
	 * @param string  $lang       The post language
	 */
	do_action( 'before_rocket_clean_post', $post, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	/**
	 * Filter URLs cache files to remove
	 *
	 * @since 1.0
	 *
	 * @param array $purge_urls List of URLs cache files to remove
	 */
	$purge_urls = apply_filters( 'rocket_post_purge_urls', $purge_urls, $post );

	// Purge all files.
	rocket_clean_files( $purge_urls );

	// Never forget to purge homepage and their pagination.
	rocket_clean_home( $lang );

	// Purge home feeds (blog & comments).
	rocket_clean_home_feeds();

	/**
	 * Fires after cache files related with the post are deleted
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post       The post object
	 * @param array   $purge_urls URLs cache files to remove
	 * @param string  $lang       The post language
	 */
	do_action( 'after_rocket_clean_post', $post, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	return true;
}
add_action( 'wp_trash_post',           'rocket_clean_post' );
add_action( 'delete_post',             'rocket_clean_post' );
add_action( 'clean_post_cache',        'rocket_clean_post' );
add_action( 'wp_update_comment_count', 'rocket_clean_post' );

/**
 * Purge WP Rocket cache when post status is changed from publish to draft.
 *
 * @since  3.4.3
 *
 * @param int   $post_id   The post ID.
 * @param array $post_data Array of unslashed post data.
 */
function rocket_clean_post_cache_on_status_change( $post_id, $post_data ) {
	if ( 'publish' !== get_post_field( 'post_status', $post_id ) || 'draft' !== $post_data['post_status'] ) {
		return;
	}

	$purge_urls = [];
	$post       = get_post( $post_id );

	// Return if $post is not an object.
	if ( ! is_object( $post ) ) {
		return;
	}
	// Get the post language.
	$i18n_plugin = rocket_has_i18n();
	$lang        = false;

	if ( 'wpml' === $i18n_plugin && ! rocket_is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
		// WPML.
		$lang = $GLOBALS['sitepress']->get_language_for_element( $post_id, 'post_' . get_post_type( $post_id ) );
	} elseif ( 'polylang' === $i18n_plugin && function_exists( 'pll_get_post_language' ) ) {
		// Polylang.
		$lang = pll_get_post_language( $post_id );
	}

	$purge_urls = rocket_get_purge_urls( $post_id, $post );

	/**
	 * Filter URLs cache files to remove
	 *
	 * @since 1.0
	 *
	 * @param array $purge_urls List of URLs cache files to remove
	 */
	$purge_urls = apply_filters( 'rocket_post_purge_urls', $purge_urls, $post );

	/**
	 * Fires before cache files related with the post are deleted
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post       The post object
	 * @param array   $purge_urls URLs cache files to remove
	 * @param string  $lang       The post language
	 */
	do_action( 'before_rocket_clean_post', $post, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	// Purge all files.
	rocket_clean_files( $purge_urls );

	// Never forget to purge homepage and their pagination.
	rocket_clean_home( $lang );

	// Purge home feeds (blog & comments).
	rocket_clean_home_feeds();

	/**
	 * Fires after cache files related with the post are deleted
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post       The post object
	 * @param array   $purge_urls URLs cache files to remove
	 * @param string  $lang       The post language
	 */
	do_action( 'after_rocket_clean_post', $post, $purge_urls, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}
add_action( 'pre_post_update', 'rocket_clean_post_cache_on_status_change', 10, 2 );

/**
 * Add pattern to clean files of connected users
 *
 * @since 2.0
 *
 * @param array $urls An array of URLs to clean.
 * @return array An array of pattern to use for clearing the cache
 */
function rocket_clean_files_users( $urls ) {
	$pattern_urls = [];
	foreach ( $urls as $url ) {
		$parse_url      = get_rocket_parse_url( $url );
		$pattern_urls[] = $parse_url['scheme'] . '://' . $parse_url['host'] . '*' . $parse_url['path'];
	}
	return $pattern_urls;
}
add_filter( 'rocket_clean_files', 'rocket_clean_files_users' );

/**
 * Return all translated version of a post when qTranslate is used.
 * Use the "rocket_post_purge_urls" filter to insert URLs of traduction post.
 *
 * @since 1.3.5
 *
 * @param  array $urls An array of URLs to clean.
 * @return array       Updated array of URLs to clean
 */
function rocket_post_purge_urls_for_qtranslate( $urls ) {
	global $q_config;

	if ( ! $urls ) {
		return [];
	}

	$i18n_plugin = rocket_has_i18n();

	if ( 'qtranslate' !== $i18n_plugin && 'qtranslate-x' !== $i18n_plugin ) {
		return $urls;
	}

	// Get all languages.
	$enabled_languages = $q_config['enabled_languages'];

	// Remove default language.
	$enabled_languages = array_diff( $enabled_languages, [ $q_config['default_language'] ] );

	// Add translate URLs.
	foreach ( $urls as $url ) {
		foreach ( $enabled_languages as $lang ) {
			if ( 'qtranslate' === $i18n_plugin ) {
				$urls[] = qtrans_convertURL( $url, $lang, true );
			} elseif ( 'qtranslate-x' === $i18n_plugin ) {
				$urls[] = qtranxf_convertURL( $url, $lang, true );
			}
		}
	}

	return $urls;
}
add_filter( 'rocket_post_purge_urls', 'rocket_post_purge_urls_for_qtranslate' );

/**
 * Purge Cache file System in Admin Bar
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0
 */
function do_admin_post_rocket_purge_cache() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( isset( $_GET['type'], $_GET['_wpnonce'] ) ) {
		$type_raw   = sanitize_key( $_GET['type'] );
		$type_array = explode( '-', $type_raw );

		$type     = $type_array[0];
		$id       = isset( $type_array[1] ) && is_numeric( $type_array[1] ) ? absint( $type_array[1] ) : 0;
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_title( wp_unslash( $_GET['taxonomy'] ) ) : '';
		$url      = '';

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'purge_cache_' . $type_raw ) ) {
			wp_nonce_ays( '' );
			return;
		}

		if ( ! current_user_can( 'rocket_purge_cache' ) ) {
			return;
		}

		switch ( $type ) {

			// Clear all cache domain.
			case 'all':
				set_transient( 'rocket_clear_cache', 'all', HOUR_IN_SECONDS );
				// Remove all cache files.
				$lang = isset( $_GET['lang'] ) && 'all' !== $_GET['lang'] ? sanitize_key( $_GET['lang'] ) : '';
				// Remove all cache files.
				rocket_clean_domain( $lang );

				if ( '' === $lang ) {
					// Remove all minify cache files.
					rocket_clean_minify();

					// Generate a new random key for minify cache file.
					$options                   = get_option( WP_ROCKET_SLUG );
					$options['minify_css_key'] = create_rocket_uniqid();
					$options['minify_js_key']  = create_rocket_uniqid();
					remove_all_filters( 'update_option_' . WP_ROCKET_SLUG );
					update_option( WP_ROCKET_SLUG, $options );
				}

				if ( get_rocket_option( 'manual_preload' ) && ( ! defined( 'WP_ROCKET_DEBUG' ) || ! WP_ROCKET_DEBUG ) ) {
					$home_url = get_rocket_i18n_home_url( $lang );

					/**
					 * Filters the arguments for the preload request being triggered after clearing the cache.
					 *
					 * @since  3.4
					 *
					 * @param array $args Request arguments.
					 */
					$args = (array) apply_filters(
						'rocket_preload_after_purge_cache_request_args',
						[
							'blocking'   => false,
							'timeout'    => 0.01,
							'user-agent' => 'WP Rocket/Homepage_Preload_After_Purge_Cache',
							'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
						]
					);

					wp_safe_remote_get( $home_url, $args );

					/**
					 * Fires after automatically preloading the homepage, which occurs after purging the cache.
					 *
					 * @since  3.5
					 *
					 * @param string $home_url URL to the homepage being preloaded.
					 * @param string $lang     The lang of the homepage.
					 * @param array  $args     Arguments used for the preload request.
					 */
					do_action( 'rocket_after_preload_after_purge_cache', $home_url, $lang, $args );
				}

				rocket_dismiss_box( 'rocket_warning_plugin_modification' );
				break;

			// Clear terms, homepage and other files associated at current post in back-end.
			case 'post':
				rocket_clean_post( $id );
				set_transient( 'rocket_clear_cache', 'post', HOUR_IN_SECONDS );
				break;

			// Clear a specific term.
			case 'term':
				rocket_clean_term( $id, $taxonomy );
				set_transient( 'rocket_clear_cache', 'term', HOUR_IN_SECONDS );
				break;

			// Clear a specific user.
			case 'user':
				rocket_clean_user( $id );
				set_transient( 'rocket_clear_cache', 'user', HOUR_IN_SECONDS );
				break;

			// Clear cache file of the current page in front-end.
			case 'url':
				$url = wp_get_referer();

				if ( 0 !== strpos( $url, 'http' ) ) {
					$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
					$url       = $parse_url['scheme'] . '://' . $parse_url['host'] . $url;
				}

				if ( home_url( '/' ) === $url ) {
					rocket_clean_home();
				} else {
					rocket_clean_files( $url );
				}
				break;

			default:
				wp_nonce_ays( '' );
				return;
		}

		/**
		 * Fires after the cache is cleared.
		 *
		 * @since  3.6
		 *
		 * @param string $type     Type of cache clearance: 'all', 'post', 'term', 'user', 'url'.
		 * @param int    $id       The post ID, term ID, or user ID being cleared. 0 when $type is not 'post', 'term', or 'user'.
		 * @param string $taxonomy The taxonomy the term being cleared belong to. '' when $type is not 'term'.
		 * @param string $url      The URL being cleared. '' when $type is not 'url'.
		 */
		do_action( 'rocket_purge_cache', $type, $id, $taxonomy, $url );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}
}
add_action( 'admin_post_purge_cache', 'do_admin_post_rocket_purge_cache' );

/**
 * Purge OPCache content in Admin Bar
 *
 * @since 2.7
 */
function do_admin_post_rocket_purge_opcache() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_purge_opcache' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'rocket_purge_opcache' ) ) {
		return;
	}

	$reset_opcache = rocket_reset_opcache();

	if ( ! $reset_opcache ) {
		$op_purge_result = [
			'result'  => 'error',
			'message' => __( 'OPcache purge failed.', 'rocket' ),
		];
	} else {
		$op_purge_result = [
			'result'  => 'success',
			'message' => __( 'OPcache successfully purged', 'rocket' ),
		];
	}

	set_transient( get_current_user_id() . '_opcache_purge_result', $op_purge_result );

	wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	die();
}
add_action( 'admin_post_rocket_purge_opcache', 'do_admin_post_rocket_purge_opcache' );

/**
 * Clean the cache when the current theme is updated.
 *
 * @param WP_Upgrader $wp_upgrader WP_Upgrader instance.
 * @param array       $hook_extra  Array of bulk item update data.
 */
function rocket_clean_cache_theme_update( $wp_upgrader, $hook_extra ) {
	if ( 'update' !== $hook_extra['action'] ) {
		return;
	}

	if ( 'theme' !== $hook_extra['type'] ) {
		return;
	}

	if ( ! is_array( $hook_extra['themes'] ) ) {
		return;
	}

	$current_theme = wp_get_theme();
	$themes        = [
		$current_theme->get_template(), // Parent theme.
		$current_theme->get_stylesheet(), // Child theme.
	];

	// Bail out if the current theme or its parent is not updating.
	if ( empty( array_intersect( $hook_extra['themes'], $themes ) ) ) {
		return;
	}

	rocket_clean_domain();
}
add_action( 'upgrader_process_complete', 'rocket_clean_cache_theme_update', 10, 2 );  // When a theme is updated.

/**
 * Purge WP Rocket cache on Slug / Permalink change.
 *
 * @since  3.4.2
 *
 * @param int   $post_id   The post ID.
 * @param array $post_data Array of unslashed post data.
 */
function rocket_clean_post_cache_on_slug_change( $post_id, $post_data ) {
	// Bail out if the post status is draft, pending or auto-draft.
	if ( in_array( get_post_field( 'post_status', $post_id ), [ 'draft', 'pending', 'auto-draft' ], true ) ) {
		return;
	}
	$post_name = get_post_field( 'post_name', $post_id );
	// Bail out if the slug hasn't changed.
	if ( $post_name === $post_data['post_name'] ) {
		return;
	}
	// Bail out if the old slug has changed, but is empty.
	if ( empty( $post_name ) ) {
		return;
	}
	rocket_clean_files( get_the_permalink( $post_id ) );
}
add_action( 'pre_post_update', 'rocket_clean_post_cache_on_slug_change', PHP_INT_MAX, 2 );
