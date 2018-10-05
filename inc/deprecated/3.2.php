<?php
defined( 'ABSPATH' ) || die( 'nope' );

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