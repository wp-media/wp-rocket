<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Add a message about Imagify on the "Upload New Media" screen and WP Rocket options page.
 *
 * @since 2.7
 * @deprecated 3.2
 */
function rocket_imagify_notice() {
	_deprecated_function( __FUNCTION__, '3.2' );
	$current_screen = get_current_screen();

	// Add the notice only on the "WP Rocket" settings, "Media Library" & "Upload New Media" screens.
	if ( 'admin_notices' === current_filter() && ( isset( $current_screen ) && 'settings_page_wprocket' !== $current_screen->base ) ) {
		return;
	}

	$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

	if ( defined( 'IMAGIFY_VERSION' ) || in_array( __FUNCTION__, (array) $boxes, true ) || 1 === get_option( 'wp_rocket_dismiss_imagify_notice' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$imagify_plugin       = 'imagify/imagify.php';
	$is_imagify_installed = rocket_is_plugin_installed( $imagify_plugin );

	$action_url = $is_imagify_installed ?
	rocket_get_plugin_activation_link( $imagify_plugin )
		:
	wp_nonce_url( add_query_arg(
		array(
			'action' => 'install-plugin',
			'plugin' => 'imagify',
		),
		admin_url( 'update.php' )
	), 'install-plugin_imagify' );

	$details_url = add_query_arg(
		array(
			'tab'       => 'plugin-information',
			'plugin'    => 'imagify',
			'TB_iframe' => true,
			'width'     => 722,
			'height'    => 949,
		),
		admin_url( 'plugin-install.php' )
	);

	$classes = $is_imagify_installed ? '' : ' install-now';
	$cta_txt = $is_imagify_installed ? esc_html__( 'Activate Imagify', 'rocket' ) : esc_html__( 'Install Imagify for Free', 'rocket' );

	$dismiss_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=rocket_ignore&box=' . __FUNCTION__ ),
		'rocket_ignore_' . __FUNCTION__
	);
	?>

	<div id="plugin-filter" class="updated plugin-card plugin-card-imagify rkt-imagify-notice">
		<a href="<?php echo $dismiss_url; ?>" class="rkt-cross"><span class="dashicons dashicons-no"></span></a>

		<p class="rkt-imagify-logo">
			<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>logo-imagify.png" srcset="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>logo-imagify.svg 2x" alt="Imagify" width="150" height="18">
		</p>
		<p class="rkt-imagify-msg">
			<?php _e( 'Speed up your website and boost your SEO by reducing image file sizes without losing quality with Imagify.', 'rocket' ); ?>
		</p>
		<p class="rkt-imagify-cta">
			<a data-slug="imagify" href="<?php echo $action_url; ?>" class="button button-primary<?php echo $classes; ?>"><?php echo $cta_txt; ?></a>
			<?php if ( ! $is_imagify_installed ) : ?>
			<br><a data-slug="imagify" data-name="Imagify Image Optimizer" class="thickbox open-plugin-details-modal" href="<?php echo $details_url; ?>"><?php _e( 'More details', 'rocket' ); ?></a>
			<?php endif; ?>
		</p>
	</div>

	<?php
}

if ( ! function_exists( 'run_rocket_preload_cache' ) ) :
	/**
	 * Launches the preload
	 *
	 * @since 2.8
	 * @author Remy Perona
	 * @deprecated 3.2
	 *
	 * @param string $spider The spider name.
	 * @param bool   $do_sitemap_preload Do the sitemap preload.
	 *
	 * @return void
	 */
	function run_rocket_preload_cache( $spider, $do_sitemap_preload = true ) {
		_deprecated_function( __FUNCTION__, '3.2' );

		// Preload cache.
		run_rocket_bot( $spider );

		if ( $do_sitemap_preload & get_rocket_option( 'sitemap_preload', false ) ) {
			$rocket_background_process = $GLOBALS['rocket_sitemap_background_process'];

			if ( method_exists( $rocket_background_process, 'cancel_process' ) ) {
				$rocket_background_process->cancel_process();
			}

			delete_transient( 'rocket_sitemap_preload_running' );
			delete_transient( 'rocket_sitemap_preload_complete' );
			run_rocket_sitemap_preload();
		}
	}
endif;

if ( ! function_exists( 'do_rocket_bot_cache_json' ) ) :
	/**
	 * Run WP Rocket Bot when a post is added, updated or deleted
	 *
	 * @since 1.3.2
	 * @deprecated 3.2
	 */
	function do_rocket_bot_cache_json() {
		_deprecated_function( __FUNCTION__, '3.2' );
		return false;
	}
endif;

if ( ! function_exists( 'rocket_process_sitemap' ) ) {
	/**
	 * Processes the sitemaps recursively
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @param string $sitemap_url URL of the sitemap.
	 * @param array  $urls An array of URLs.
	 * @return array Empty array or array containing URLs
	 */
	function rocket_process_sitemap( $sitemap_url, $urls = array() ) {
		_deprecated_function( __FUNCTION__, '3.2' );
		$tmp_urls = array();

		/**
		 * Filters the arguments for the sitemap preload request
		 *
		 * @since 2.10.8
		 * @author Remy Perona
		 *
		 * @param array $args Arguments for the request.
		 */
		$args = apply_filters( 'rocket_preload_sitemap_request_args', array(
			'user-agent' => 'WP Rocket/Sitemaps',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
		) );

		$sitemap = wp_remote_get( esc_url( $sitemap_url ), $args );

		if ( is_wp_error( $sitemap ) ) {
			return array();
		}

		$xml_data = wp_remote_retrieve_body( $sitemap );

		if ( empty( $xml_data ) ) {
			return array();
		}

		libxml_use_internal_errors( true );

		$xml = simplexml_load_string( $xml_data );

		if ( false === $xml ) {
			libxml_clear_errors();
			return array();
		}

		$url_count = count( $xml->url );

		if ( $url_count > 0 ) {
			for ( $i = 0; $i < $url_count; $i++ ) {
				$page_url   = (string) $xml->url[ $i ]->loc;
				$tmp_urls[] = $page_url;
			}
		} else {
			// Sub sitemap?
			$sitemap_children = count( $xml->sitemap );
			if ( $sitemap_children > 0 ) {
				for ( $i = 0; $i < $sitemap_children; $i++ ) {
					$sub_sitemap_url = (string) $xml->sitemap[ $i ]->loc;
					$urls            = rocket_process_sitemap( $sub_sitemap_url, $urls );
				}
			}
		}

		$urls = array_merge( $urls, $tmp_urls );
		return $urls;
	}
}

if ( ! function_exists( 'rocket_sitemap_preload_complete' ) ) {
	/**
	 * This notice is displayed after the sitemap preload is complete
	 *
	 * @since 2.11
	 * @deprecated 3.2
	 * @author Remy Perona
	 */
	function rocket_sitemap_preload_complete() {
		_deprecated_function( __FUNCTION__, '3.2' );
		$screen = get_current_screen();

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$result = get_transient( 'rocket_sitemap_preload_complete' );
		if ( false === $result ) {
			return;
		}

		delete_transient( 'rocket_sitemap_preload_complete' );

		rocket_notice_html( array(
			// translators: %d is the number of pages preloaded.
			'message' => sprintf( __( 'Sitemap preload: %d pages have been cached.', 'rocket' ), $result ),
		) );
	}
}

if ( ! function_exists( 'rocket_sitemap_preload_running' ) ) {
	/**
	 * This notice is displayed when the sitemap preload is running
	 *
	 * @since 2.11
	 * @deprecated 3.2
	 * @author Remy Perona
	 */
	function rocket_sitemap_preload_running() {
		_deprecated_function( __FUNCTION__, '3.2' );
		$screen = get_current_screen();

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$running = get_transient( 'rocket_sitemap_preload_running' );
		if ( false === $running ) {
			return;
		}

		rocket_notice_html( array(
			// translators: %d = Number of pages preloaded.
			'message' => sprintf( __( 'Sitemap preload: %d uncached pages have now been preloaded. (refresh to see progress)', 'rocket' ), $running ),
		) );
	}
}

if ( ! function_exists( 'run_rocket_bot_after_clean_post' ) ) {
	/**
	 * Actions to be done after the purge cache files of a post
	 * By Default, this hook call the WP Rocket Bot (cache json)
	 *
	 * @deprecated 3.2
	 * @since 1.3.0
	 *
	 * @param object $post The post object.
	 * @param array  $purge_urls An array of URLs to clean.
	 * @param string $lang The language to clean.
	 */
	function run_rocket_bot_after_clean_post( $post, $purge_urls, $lang ) {
		_deprecated_function( __FUNCTION__, '3.2' );
		// Run robot only if post is published.
		if ( 'publish' !== $post->post_status ) {
			return false;
		}

		// Add Homepage URL to $purge_urls for bot crawl.
		array_push( $purge_urls, get_rocket_i18n_home_url( $lang ) );

		// Add default WordPress feeds (posts & comments).
		array_push( $purge_urls, get_feed_link() );
		array_push( $purge_urls, get_feed_link( 'comments_' ) );

		// Get the author page.
		$purge_author = array( get_author_posts_url( $post->post_author ) );

		// Get all dates archive page.
		$purge_dates = get_rocket_post_dates_urls( $post->ID );

		// Remove dates archives page and author page to preload cache.
		$purge_urls = array_diff( $purge_urls, $purge_dates, $purge_author );

		// Create json file and run WP Rocket Bot.
		$json_encode_urls = '["' . implode( '","', array_filter( $purge_urls ) ) . '"]';
		if ( rocket_put_content( WP_ROCKET_PATH . 'cache.json', $json_encode_urls ) ) {
			global $do_rocket_bot_cache_json;
			$do_rocket_bot_cache_json = true;
		}
	}
}

if ( ! function_exists( 'run_rocket_bot_after_clean_term' ) ) {
	/**
	 * Actions to be done after the purge cache files of a term
	 * By Default, this hook call the WP Rocket Bot (cache json)
	 *
	 * @deprecated 3.2
	 * @since 2.6.8
	 *
	 * @param object $post The post object.
	 * @param array  $purge_urls An array of URLs to clean.
	 * @param string $lang The language to clean.
	 */
	function run_rocket_bot_after_clean_term( $post, $purge_urls, $lang ) {
		_deprecated_function( __FUNCTION__, '3.2' );
		// Add Homepage URL to $purge_urls for bot crawl.
		array_push( $purge_urls, get_rocket_i18n_home_url( $lang ) );

		// Create json file and run WP Rocket Bot.
		$json_encode_urls = '["' . implode( '","', array_filter( $purge_urls ) ) . '"]';
		if ( rocket_put_content( WP_ROCKET_PATH . 'cache.json', $json_encode_urls ) ) {
			global $do_rocket_bot_cache_json;
			$do_rocket_bot_cache_json = true;
		}
	}
}

if ( ! function_exists( 'rocket_clean_directory_for_default_language_on_wpml' ) ) {
	/**
	 * Conflict with WPML: Clear the homepage when the "Use directory for default language" option is activated.
	 *
	 * @since 2.6.8
	 * @deprecated 3.2.4
	 */
	function rocket_clean_directory_for_default_language_on_wpml() {
		_deprecated_function( __FUNCTION__, '3.2.4' );
		$option = get_option( 'icl_sitepress_settings' );

		if ( 1 === $option['language_negotiation_type'] && $option['urls']['directory_for_default_language'] ) {
			rocket_clean_files( home_url() );
		}
	}
}

if ( ! function_exists( 'rocket_fetch_and_cache_busting' ) ) {
	/**
	 * Fetch and save the cache busting file content
	 *
	 * @since 2.10
	 * @deprecated 3.2.5
	 * @author Remy Perona
	 *
	 * @param string $src                 Original URL of the asset.
	 * @param array  $cache_busting_paths Paths used to generated the cache busting file.
	 * @param string $abspath_src         Absolute path to the asset.
	 * @param string $current_filter      Current filter value.
	 * @return bool true if successful, false otherwise
	 */
	function rocket_fetch_and_cache_busting( $src, $cache_busting_paths, $abspath_src, $current_filter ) {
		_deprecated_function( __FUNCTION__, '3.2.5' );
		if ( wp_is_stream( $src ) ) {
			$response = wp_remote_get( $src );
			$content  = wp_remote_retrieve_body( $response );
		} else {
			$content = rocket_direct_filesystem()->get_contents( $src );
		}

		if ( ! $content ) {
			return false;
		}

		if ( 'style_loader_src' === $current_filter ) {
			/**
			 * Filters the Document Root path to use during CSS minification to rewrite paths
			 *
			 * @since 2.7
			 *
			 * @param string The Document Root path.
			*/
			$document_root = apply_filters( 'rocket_min_documentRoot', wp_normalize_path( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) );

			// Rewrite import/url in CSS content to add the absolute path to the file.
			$content = Minify_CSS_UriRewriter::rewrite( $content, dirname( $abspath_src ), $document_root );
		}

		if ( ! rocket_direct_filesystem()->is_dir( $cache_busting_paths['bustingpath'] ) ) {
			rocket_mkdir_p( $cache_busting_paths['bustingpath'] );
		}

		rocket_mkdir_p( dirname( $cache_busting_paths['filepath'] ) );

		return rocket_put_content( $cache_busting_paths['filepath'], $content );
	}
}
