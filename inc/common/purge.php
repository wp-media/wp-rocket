<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Launch hooks that deletes all the cache domain.
add_action( 'switch_theme', 'rocket_clean_domain' );  // When user change theme.
add_action( 'user_register', 'rocket_clean_domain' );  // When a user is added.
add_action( 'profile_update', 'rocket_clean_domain' );  // When a user is updated.
add_action( 'deleted_user', 'rocket_clean_domain' );  // When a user is deleted.
add_action( 'wp_update_nav_menu', 'rocket_clean_domain' );  // When a custom menu is update.
add_action( 'update_option_sidebars_widgets', 'rocket_clean_domain' );  // When you change the order of widgets.
add_action( 'update_option_category_base', 'rocket_clean_domain' );  // When category permalink prefix is update.
add_action( 'update_option_tag_base', 'rocket_clean_domain' );  // When tag permalink prefix is update.
add_action( 'permalink_structure_changed', 'rocket_clean_domain' );  // When permalink structure is update.
add_action( 'create_term', 'rocket_clean_domain' );  // When a term is created.
add_action( 'edited_terms', 'rocket_clean_domain' );  // When a term is updated.
add_action( 'delete_term', 'rocket_clean_domain' );  // When a term is deleted.
add_action( 'add_link', 'rocket_clean_domain' );  // When a link is added.
add_action( 'edit_link', 'rocket_clean_domain' );  // When a link is updated.
add_action( 'delete_link', 'rocket_clean_domain' );  // When a link is deleted.
add_action( 'customize_save', 'rocket_clean_domain' );  // When customizer is saved.
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'rocket_clean_domain' ); // When location of a menu is updated.
add_action( 'upgrader_process_complete', 'rocket_clean_cache_theme_update', 10, 2 );  // When a theme is updated.

/**
 * Purge cache When a widget is updated
 *
 * @since 1.1.1
 *
 * @param object $instance Widget instance.
 * @return object Widget instance
 */
function rocket_widget_update_callback( $instance ) {
	rocket_clean_domain();
	return $instance;
}
add_filter( 'widget_update_callback', 'rocket_widget_update_callback' );

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
		return;
	}

	$done[ $post_id ] = 1;

	if ( defined( 'DOING_AUTOSAVE' ) ) {
		return;
	}

	$purge_urls = [];

	// Get all post infos if the $post object was not supplied.
	if ( is_null( $post ) ) {
		$post = get_post( $post_id );
	}

	// Return if $post is not an object.
	if ( ! is_object( $post ) ) {
		return;
	}

	// No purge for specific conditions.
	if ( 'auto-draft' === $post->post_status || empty( $post->post_type ) || 'nav_menu_item' === $post->post_type || 'attachment' === $post->post_type ) {
		return;
	}

	// Don't purge if post's post type is not public or not publicly queryable.
	$post_type = get_post_type_object( $post->post_type );
	if ( ! is_object( $post_type ) || true !== $post_type->public ) {
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

	// Get the permalink structure.
	$permalink_structure = get_rocket_sample_permalink( $post_id );

	// Get permalink.
	$permalink = str_replace( [ '%postname%', '%pagename%' ], $permalink_structure[1], $permalink_structure[0] );

	// Add permalink.
	if ( rocket_extract_url_component( $permalink, PHP_URL_PATH ) !== '/' ) {
		array_push( $purge_urls, $permalink );
	}

	// Add Posts page.
	if ( 'post' === $post->post_type && (int) get_option( 'page_for_posts' ) > 0 ) {
		array_push( $purge_urls, get_permalink( get_option( 'page_for_posts' ) ) );
	}

	// Add Post Type archive.
	if ( 'post' !== $post->post_type ) {
		$post_type_archive = get_post_type_archive_link( get_post_type( $post_id ) );
		if ( $post_type_archive ) {
			$post_type_archive = trailingslashit( $post_type_archive );
			array_push( $purge_urls, $post_type_archive . 'index.html' );
			array_push( $purge_urls, $post_type_archive . 'index.html_gzip' );
			array_push( $purge_urls, $post_type_archive . $GLOBALS['wp_rewrite']->pagination_base );
		}
	}

	// Add next post.
	$next_post = get_adjacent_post( false, '', false );
	if ( $next_post ) {
		array_push( $purge_urls, get_permalink( $next_post ) );
	}

	// Add next post in same category.
	$next_in_same_cat_post = get_adjacent_post( true, '', false );
	if ( $next_in_same_cat_post && $next_in_same_cat_post !== $next_post ) {
		array_push( $purge_urls, get_permalink( $next_in_same_cat_post ) );
	}

	// Add previous post.
	$previous_post = get_adjacent_post( false, '', true );
	if ( $previous_post ) {
		array_push( $purge_urls, get_permalink( $previous_post ) );
	}

	// Add previous post in same category.
	$previous_in_same_cat_post = get_adjacent_post( true, '', true );
	if ( $previous_in_same_cat_post && $previous_in_same_cat_post !== $previous_post ) {
		array_push( $purge_urls, get_permalink( $previous_in_same_cat_post ) );
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

		foreach ( $cache_purge_pages as $page ) {
			$page = trailingslashit( $home_url ) . $page;
			array_push( $purge_urls, $page );
		}
	}

	// Add all terms archive page to purge.
	$purge_terms = get_rocket_post_terms_urls( $post_id );
	if ( count( $purge_terms ) ) {
		$purge_urls = array_merge( $purge_urls, $purge_terms );
	}

	// Add all dates archive page to purge.
	$purge_dates = get_rocket_post_dates_urls( $post_id );
	if ( count( $purge_dates ) ) {
		$purge_urls = array_merge( $purge_urls, $purge_dates );
	}

	// Add the author page.
	$purge_author = array( get_author_posts_url( $post->post_author ) );
	$purge_urls   = array_merge( $purge_urls, $purge_author );

	// Add all parents.
	$parents = get_post_ancestors( $post_id );
	if ( (bool) $parents ) {
		foreach ( $parents as $parent_id ) {
			array_push( $purge_urls, get_permalink( $parent_id ) );
		}
	}

	/**
	 * Fires before cache files related with the post are deleted
	 *
	 * @since 1.3.0
	 *
	 * @param WP_Post $post       The post object
	 * @param array   $purge_urls URLs cache files to remove
	 * @param string  $lang       The post language
	 */
	do_action( 'before_rocket_clean_post', $post, $purge_urls, $lang );

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
	do_action( 'after_rocket_clean_post', $post, $purge_urls, $lang );
}
add_action( 'wp_trash_post',           'rocket_clean_post' );
add_action( 'delete_post',             'rocket_clean_post' );
add_action( 'clean_post_cache',        'rocket_clean_post', 10, 2 );
add_action( 'wp_update_comment_count', 'rocket_clean_post' );

/**
 * Add pattern to clean files of connected users
 *
 * @since 2.0
 *
 * @param array $urls An array of URLs to clean.
 * @return array An array of pattern to use for clearing the cache
 */
function rocket_clean_files_users( $urls ) {
	$pattern_urls = array();
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
function do_admin_post_rocket_purge_cache() {
	if ( isset( $_GET['type'], $_GET['_wpnonce'] ) ) {

		$_type     = explode( '-', $_GET['type'] );
		$_type     = reset( $_type );
		$_id       = explode( '-', $_GET['type'] );
		$_id       = end( $_id );
		$_taxonomy = isset( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : false;

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'purge_cache_' . $_GET['type'] ) ) {
			wp_nonce_ays( '' );
		}

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		switch ( $_type ) {

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

					// Remove cache busting files.
					rocket_clean_cache_busting();

					// Generate a new random key for minify cache file.
					$options                   = get_option( WP_ROCKET_SLUG );
					$options['minify_css_key'] = create_rocket_uniqid();
					$options['minify_js_key']  = create_rocket_uniqid();
					remove_all_filters( 'update_option_' . WP_ROCKET_SLUG );
					update_option( WP_ROCKET_SLUG, $options );
				}

				rocket_dismiss_box( 'rocket_warning_plugin_modification' );

				break;

			// Clear terms, homepage and other files associated at current post in back-end.
			case 'post':
				rocket_clean_post( $_id );
				set_transient( 'rocket_clear_cache', 'post', HOUR_IN_SECONDS );
				break;

			// Clear a specific term.
			case 'term':
				rocket_clean_term( $_id, $_taxonomy );
				set_transient( 'rocket_clear_cache', 'term', HOUR_IN_SECONDS );
				break;

			// Clear a specific user.
			case 'user':
				rocket_clean_user( $_id );
				set_transient( 'rocket_clear_cache', 'user', HOUR_IN_SECONDS );
				break;

			// Clear cache file of the current page in front-end.
			case 'url':
				$referer = wp_get_referer();

				if ( 0 !== strpos( $referer, 'http' ) ) {
					$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
					$referer   = $parse_url['scheme'] . '://' . $parse_url['host'] . $referer;
				}

				if ( home_url( '/' ) === $referer ) {
					rocket_clean_home();
				} else {
					rocket_clean_files( $referer );
				}
				break;

			default:
				wp_nonce_ays( '' );
				break;
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}
}
add_action( 'admin_post_purge_cache', 'do_admin_post_rocket_purge_cache' );

/**
 * Purge OPCache content in Admin Bar
 *
 * @since 2.7
 */
function do_admin_post_rocket_purge_opcache() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_opcache' ) ) {
		wp_nonce_ays( '' );
	}

	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	rocket_reset_opcache();

	wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	die();
}
add_action( 'admin_post_rocket_purge_opcache', 'do_admin_post_rocket_purge_opcache' );

/**
 * Purge CloudFlare cache
 *
 * @since 2.5
 */
function do_admin_post_rocket_purge_cloudflare() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_cloudflare' ) ) {
		wp_nonce_ays( '' );
	}

	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	// Purge CloudFlare.
	$cf_purge = rocket_purge_cloudflare();

	if ( is_wp_error( $cf_purge ) ) {
		$cf_purge_result = array(
			'result'  => 'error',
			// translators: %s = CloudFare API return message.
			'message' => sprintf( __( 'Cloudflare cache purge error: %s', 'rocket' ), $cf_purge->get_error_message() ),
		);
	} else {
		$cf_purge_result = array(
			'result'  => 'success',
			'message' => __( 'Cloudflare cache successfully purged', 'rocket' ),
		);
	}

	set_transient( $GLOBALS['current_user']->ID . '_cloudflare_purge_result', $cf_purge_result );

	wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	die();
}
add_action( 'admin_post_rocket_purge_cloudflare', 'do_admin_post_rocket_purge_cloudflare' );

/**
 * Clean the cache when the current theme is updated
 *
 * @param WP_Upgrader $wp_upgrader WP_Upgrader instance.
 * @param array       $hook_extra  Array of bulk item update data.
 * @return void
 */
function rocket_clean_cache_theme_update( $wp_upgrader, $hook_extra ) {
	if ( 'update' !== $hook_extra['action'] ) {
		return;
	}

	if ( 'theme' !== $hook_extra['type'] ) {
		return;
	}

	$current_theme = wp_get_theme();

	$themes = [
		$current_theme->get_template(), // Parent theme.
		$current_theme->get_stylesheet(), // Child theme.
	];

	if ( ! is_array( $hook_extra['themes'] ) ) {
		return;
	}

	if ( ! array_intersect( $hook_extra['themes'], $themes ) ) {
		return;
	}

	rocket_clean_domain();
}
