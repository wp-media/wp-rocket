<?php

namespace WP_Rocket\Engine\Preload;

/**
 * Sitemap preload.
 *
 * @since 3.2
 * @author Remy Perona
 */
class Sitemap extends AbstractPreload {

	/**
	 * An ID used in the "running" transient’s name.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @var string
	 */
	const PRELOAD_ID = 'sitemap';

	/**
	 * Flag to track sitemap read failures.
	 *
	 * @since 3.3
	 * @author Arun Basil Lal
	 *
	 * @var bool
	 * @access private
	 */
	private $sitemap_error = false;

	/**
	 * Launches the sitemap preload.
	 *
	 * @since  3.2
	 * @access public
	 * @author Remy Perona
	 *
	 * @param array $sitemaps Sitemaps to use for preloading.
	 * @return void
	 */
	public function run_preload( array $sitemaps ) {
		if ( ! $sitemaps ) {
			return;
		}

		$urls = [];

		foreach ( $sitemaps as $sitemap_type => $sitemap_url ) {
			/**
			 * Fires before WP Rocket sitemap preload is called for a sitemap URL.
			 *
			 * @since 2.8
			 *
			 * @param string $sitemap_type The sitemap identifier.
			 * @param string $sitemap_url  Sitemap URL to be crawled.
			*/
			do_action( 'before_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			$urls = $this->process_sitemap( $sitemap_url, $urls );

			/**
			 * Fires after WP Rocket sitemap preload was called for a sitemap URL.
			 *
			 * @since 2.8
			 *
			 * @param string $sitemap_type The sitemap identifier.
			 * @param string $sitemap_url  Sitemap URL crawled.
			*/
			do_action( 'after_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		}

		if ( true === $this->sitemap_error ) {
			// Attempt to use the fallback method.
			$urls = $this->get_urls( $urls );
		}

		if ( ! $urls ) {
			return;
		}

		$preload = 0;

		foreach ( $urls as $item ) {
			$path = wp_parse_url( $item['url'], PHP_URL_PATH );

			if ( isset( $path ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $path ) ) {
				continue;
			}

			$this->preload_process->push_to_queue( $item );
			$preload++;
		}

		if ( ! $preload ) {
			return;
		}

		set_transient( $this->get_running_transient_name(), 0 );
		$this->preload_process->save()->dispatch();
	}

	/**
	 * Processes the sitemaps recursively.
	 *
	 * @since  3.2
	 * @since  3.5 Now private.
	 * @author Remy Perona
	 *
	 * @param  string $sitemap_url URL of the sitemap.
	 * @param  array  $urls        An array of arrays.
	 * @return array {
	 *     Array values are arrays described as follow.
	 *     Array keys are an identifier based on the URL path.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 *     @type string $source An identifier related to the source of the preload (e.g. RELOAD_ID).
	 * }
	 */
	private function process_sitemap( $sitemap_url, array $urls = [] ) {
		$this->sitemap_error = false;

		/**
		 * Filters the arguments for the sitemap preload request.
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
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$sitemap          = wp_remote_get( esc_url_raw( $sitemap_url ), $args );
		$errors           = get_transient( 'rocket_preload_errors' );
		$errors           = is_array( $errors ) ? $errors : [];
		$errors['errors'] = isset( $errors['errors'] ) && is_array( $errors['errors'] ) ? $errors['errors'] : [];

		if ( is_wp_error( $sitemap ) ) {
			// Translators: %1$s is a XML sitemap URL, %2$s is the error message, %3$s = opening link tag, %4$s = closing link tag.
			$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. Could not gather links on %1$s because of the following error: %2$s. %3$sLearn more%4$s.', 'rocket' ), $sitemap_url, $sitemap->get_error_message(), '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return $urls;
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
			return $urls;
		}

		$xml_data = wp_remote_retrieve_body( $sitemap );

		if ( empty( $xml_data ) ) {

			// Translators: %1$s is a XML sitemap URL, %2$s = opening link tag, %3$s = closing link tag.
			$errors['errors'][] = sprintf( __( 'Sitemap preload encountered an error. Could not collect links from %1$s because the file is empty. %2$sLearn more%3$s.', 'rocket' ), $sitemap_url, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

			$this->sitemap_error = true;

			set_transient( 'rocket_preload_errors', $errors );
			return $urls;
		}

		if ( ! function_exists( 'simplexml_load_string' ) ) {

			$this->sitemap_error = true;
			return $urls;
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
			return $urls;
		}

		$url_count        = count( $xml->url );
		$sitemap_children = count( $xml->sitemap );

		if ( $url_count > 0 ) {
			$mobile_preload = $this->preload_process->is_mobile_preload_enabled();

			for ( $i = 0; $i < $url_count; $i++ ) {
				$url = (string) $xml->url[ $i ]->loc;

				if ( ! $url ) {
					continue;
				}

				$namespaces = $xml->url[ $i ]->getNamespaces( true );
				$path       = $this->get_url_identifier( $url );
				$mobile_key = $path . self::MOBILE_SUFFIX;

				if ( ! empty( $namespaces['mobile'] ) ) {
					// According to the sitemap, this URL is dedicated to mobile devices.
					if ( isset( $urls[ $mobile_key ] ) ) {
						continue;
					}

					$urls[ $mobile_key ] = [
						'url'    => $url,
						'mobile' => true,
						'source' => self::PRELOAD_ID,
					];
				} else {
					if ( ! isset( $urls[ $path ] ) ) {
						$urls[ $path ] = [
							'url'    => $url,
							'mobile' => false,
							'source' => self::PRELOAD_ID,
						];
					}

					if ( $mobile_preload && ! isset( $urls[ $mobile_key ] ) ) {
						$urls[ $mobile_key ] = [
							'url'    => $url,
							'mobile' => true,
							'source' => self::PRELOAD_ID,
						];
					}
				}
			}

			return $urls;
		}

		if ( ! $sitemap_children ) {
			return $urls;
		}

		for ( $i = 0; $i < $sitemap_children; $i++ ) {
			$sub_sitemap_url = (string) $xml->sitemap[ $i ]->loc;
			$urls            = $this->process_sitemap( $sub_sitemap_url, $urls );
		}

		return $urls;
	}

	/**
	 * Get URLs from WordPress.
	 *
	 * Used as a fallback when extracting URLs from sitemap fails.
	 *
	 * @since  3.3
	 * @since  3.5 New $urls argument.
	 * @since  3.5 Now private.
	 * @author Arun Basil Lal
	 *
	 * @link https://github.com/wp-media/wp-rocket/issues/1306
	 *
	 * @param  array $urls An array of arrays.
	 * @return array {
	 *     Array values are arrays described as follow.
	 *     Array keys are an identifier based on the URL path.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 *     @type string $source An identifier related to the source of the preload (e.g. RELOAD_ID).
	 * }
	 */
	private function get_urls( array $urls = [] ) {
		// Get public post types.
		$post_types = get_post_types( [ 'public' => true ] );
		$post_types = array_filter( $post_types, 'is_post_type_viewable' );

		/**
		 * Filters the arguments for get_posts.
		 *
		 * @since 3.3
		 * @author Arun Basil Lal
		 *
		 * @param array $args Arguments for get_posts.
		 */
		$args = apply_filters(
			'rocket_preload_sitemap_fallback_request_args',
			[
				'fields'         => 'ids',
				'numberposts'    => 1000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
				'posts_per_page' => -1,
				'post_type'      => $post_types,
			]
		);

		$all_posts      = get_posts( $args );
		$mobile_preload = $this->preload_process->is_mobile_preload_enabled();

		foreach ( $all_posts as $post ) {
			$permalink = get_permalink( $post );

			if ( false === $permalink ) {
				continue;
			}

			$path = $this->get_url_identifier( $permalink );

			$urls[ $path ] = [
				'url'    => $permalink,
				'mobile' => false,
				'source' => self::PRELOAD_ID,
			];

			if ( ! $mobile_preload ) {
				continue;
			}

			$urls[ $path . self::MOBILE_SUFFIX ] = [
				'url'    => $permalink,
				'mobile' => true,
				'source' => self::PRELOAD_ID,
			];
		}

		return $urls;
	}
}
