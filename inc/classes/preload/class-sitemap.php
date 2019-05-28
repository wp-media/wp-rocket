<?php
namespace WP_Rocket\Preload;

/**
 * Sitemap preload
 *
 * @since 3.2
 * @author Remy Perona
 */
class Sitemap extends Abstract_Preload {
	/**
	 * Flag to track sitemap read failures
	 *
	 * @since 3.3
	 * @author Arun Basil Lal
	 *
	 * @var bool
	 * @access private
	 */
	private $sitemap_error = false;

	/**
	 * Launches the sitemap preload
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $sitemaps Sitemaps to use for preloading.
	 * @return void
	 */
	public function run_preload( $sitemaps ) {
		if ( ! $sitemaps ) {
			return;
		}

		$urls_group = [];

		foreach ( $sitemaps as $sitemap_type => $sitemap_url ) {
			/**
			 * Fires before WP Rocket sitemap preload is called for a sitemap URL
			 *
			 * @since 2.8
			 *
			 * @param string $sitemap_type  the sitemap identifier
			 * @param string $sitemap_url sitemap URL to be crawler
			*/
			do_action( 'before_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url ); // WPCS: prefix ok.

			$urls_group[] = $this->process_sitemap( $sitemap_url );

			/**
			 * Fires after WP Rocket sitemap preload was called for a sitemap URL
			 *
			 * @since 2.8
			 *
			 * @param string $sitemap_type  the sitemap identifier
			 * @param string $sitemap_url sitemap URL crawled
			*/
			do_action( 'after_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url ); // WPCS: prefix ok.
		}

		if ( true === $this->sitemap_error ) {
			// Attempt to use the fallback method.
			$fallback_urls = $this->get_urls();

			if ( ! empty( $fallback_urls ) ) {
				$urls_group[] = $fallback_urls;
			}
		}

		$urls_group = array_filter( $urls_group );

		if ( ! $urls_group ) {
			return;
		}

		$preload = 0;

		foreach ( $urls_group as $urls ) {
			$urls = array_flip( array_flip( $urls ) );
			foreach ( $urls as $url ) {
				$path = wp_parse_url( $url, PHP_URL_PATH );

				if ( isset( $path ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $path ) ) {
					continue;
				}

				$this->preload_process->push_to_queue( $url );
				$preload++;
			}
		}

		if ( 0 === $preload ) {
			return;
		}

		set_transient( 'rocket_preload_running', 0 );
		$this->preload_process->save()->dispatch();
	}

	/**
	 * Processes the sitemaps recursively
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param string $sitemap_url URL of the sitemap.
	 * @param array  $urls        An array of URLs.
	 * @return array
	 */
	public function process_sitemap( $sitemap_url, $urls = [] ) {
		$tmp_urls = [];

		/**
		 * Filters the arguments for the sitemap preload request
		 *
		 * @since 2.10.8
		 * @author Remy Perona
		 *
		 * @param array $args Arguments for the request.
		 */
		$args = apply_filters(
			'rocket_preload_sitemap_request_args',
			[
				'timeout'    => 10,
				'user-agent' => 'WP Rocket/Sitemaps',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // WPCS: prefix ok.
			]
		);

		$sitemap          = wp_remote_get( esc_url( $sitemap_url ), $args );
		$errors           = get_transient( 'rocket_preload_errors' );
		$errors           = is_array( $errors ) ? $errors : [];
		$errors['errors'] = isset( $errors['errors'] ) && is_array( $errors['errors'] ) ? $errors['errors'] : [];

		if ( is_wp_error( $sitemap ) ) {
			// Translators: %1$s is a XML sitemap URL, %2$s is the error message, %3$s = opening link tag, %4$s = closing link tag..
			$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. Could not gather links on %1$s because of the following error: %2$s. %3$sLearn more%4$s.', 'rocket' ), $sitemap_url, $sitemap->get_error_message(), '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		$response_code = wp_remote_retrieve_response_code( $sitemap );

		if ( 200 !== $response_code ) {
			switch ( $response_code ) {
				case 401:
				case 403:
					// Translators: %1$s is an URL, %2$s is the HTTP response code, %3$s = opening link tag, %4$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. %1$s is not accessible to due to the following response code: %2$s. Security measures could be preventing access. %3$sLearn more%4$s.', 'rocket' ), $sitemap_url, $response_code, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					break;
				case 404:
					// Translators: %1$s is an URL, %2$s = opening link tag, %3$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. %1$s is not accessible to due to the following response code: 404. Please make sure you entered the correct sitemap URL and it is accessible in your browser. %2$sLearn more%3$s.', 'rocket' ), $sitemap_url, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					break;
				case 500:
					// Translators: %1$s is an URL, %2$s = opening link tag, %3$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. %1$s is not accessible to due to the following response code: 500. Please check with your web host about server access. %2$sLearn more%3$s.', 'rocket' ), $sitemap_url, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					break;
				default:
					// Translators: %1$s is an URL, %2$s is the HTTP response code, %3$s = opening link tag, %4$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. Could not gather links on %1$s because it returned the following response code: %2$s. %3$sLearn more%4$s.', 'rocket' ), $sitemap_url, $response_code, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					break;
			}

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		$xml_data = wp_remote_retrieve_body( $sitemap );

		if ( empty( $xml_data ) ) {

			// Translators: %1$s is a XML sitemap URL, %2$s = opening link tag, %3$s = closing link tag.
			$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. Could not collect links from %1$s because the file is empty. %2$sLearn more%3$s.', 'rocket' ), $sitemap_url, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		if ( ! function_exists( 'simplexml_load_string' ) ) {

			$this->sitemap_error = true;
			return [];
		}

		libxml_use_internal_errors( true );

		$xml = simplexml_load_string( $xml_data );

		if ( false === $xml ) {
			$errors['errors'][] = sprintf(
				// Translators: %1$s is a XML sitemap URL, %2$s = opening link tag, %3$s = closing link tag.
				__( 'Sitemap preload encountered an error. Could not collect links from %1$s because of an error during the XML sitemap parsing. %2$sLearn more%3$s.', 'rocket' ),
				$sitemap_url,
				'<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">',
				'</a>'
			);

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		$url_count        = count( $xml->url );
		$sitemap_children = count( $xml->sitemap );

		if ( $url_count > 0 ) {
			for ( $i = 0; $i < $url_count; $i++ ) {
				$tmp_urls[] = (string) $xml->url[ $i ]->loc;
			}
		} elseif ( $sitemap_children > 0 ) {
			for ( $i = 0; $i < $sitemap_children; $i++ ) {
				$sub_sitemap_url = (string) $xml->sitemap[ $i ]->loc;
				$urls            = $this->process_sitemap( $sub_sitemap_url, $urls );
			}
		}

		$urls = array_merge( $urls, $tmp_urls );
		return $urls;
	}

	/**
	 * Get URLs from WordPress
	 *
	 * Used as a fallback when extracting URLs from sitemap fails.
	 *
	 * @since 3.3
	 * @author Arun Basil Lal
	 *
	 * @link https://github.com/wp-media/wp-rocket/issues/1306
	 *
	 * @return array $urls Array of permalinks.
	 */
	public function get_urls() {

		$urls = [];

		// Get public post types.
		$post_types = get_post_types( array( 'public' => true ) );
		$post_types = array_filter( $post_types, 'is_post_type_viewable' );

		/**
		 * Filters the arguments for get_posts
		 *
		 * @since 3.3
		 * @author Arun Basil Lal
		 *
		 * @param array $args Arguments for get_posts
		 */
		$args = apply_filters(
			'rocket_preload_sitemap_fallback_request_args',
			[
				'fields'         => 'ids',
				'numberposts'    => 1000,
				'posts_per_page' => -1,
				'post_type'      => $post_types,
			]
		);

		$all_posts = get_posts( $args );

		foreach ( $all_posts as $post ) {
			$permalink = get_permalink( $post );

			if ( false !== $permalink ) {
				$urls[] = $permalink;
			}
		}

		return $urls;
	}
}
