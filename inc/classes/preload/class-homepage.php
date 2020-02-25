<?php
namespace WP_Rocket\Preload;

/**
 * Preloads the homepage and the internal URLs on it.
 *
 * @since 3.2
 * @author Remy Perona
 */
class Homepage extends Abstract_Preload {

	/**
	 * An ID used in the "running" transient’s name.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @var string
	 */
	const PRELOAD_ID = 'homepage';

	/**
	 * Gets the internal URLs on the homepage and sends them to the preload queue.
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $home_urls Homepages URLs to preload.
	 * @return void
	 */
	public function preload( array $home_urls ) {
		if ( ! $home_urls ) {
			return;
		}

		$home_urls = array_map( [ $this->preload_process, 'format_item' ], $home_urls );
		$home_urls = array_filter( $home_urls );

		if ( ! $home_urls ) {
			return;
		}

		if ( $this->preload_process->is_mobile_preload_enabled() ) {
			foreach ( $home_urls as $home_item ) {
				if ( $home_item['mobile'] ) {
					continue;
				}

				$home_urls[] = [
					'url'    => $home_item['url'],
					'mobile' => true,
				];
			}
		}

		$preload_urls = [];
		$nbr_homes    = 0;

		foreach ( $home_urls as $home_item ) {
			$urls = $this->get_urls( $home_item );

			if ( false !== $urls && ! $home_item['mobile'] ) {
				// Homepage successfully preloaded (and not mobile).
				++$nbr_homes;
			}

			if ( ! $urls ) {
				continue;
			}

			$home_host = wp_parse_url( $home_item['url'], PHP_URL_HOST );

			foreach ( $urls as $url ) {
				if ( ! $this->should_preload( $url, $home_item['url'], $home_host ) ) {
					continue;
				}

				$path = $this->get_url_identifier( $url );

				if ( ! $home_item['mobile'] && ! isset( $preload_urls[ $path ] ) ) {
					// Not a URL for mobile.
					$preload_urls[ $path ] = [
						'url'    => $url,
						'mobile' => false,
						'source' => self::PRELOAD_ID,
					];
				}

				if ( $home_item['mobile'] && ! isset( $preload_urls[ $path . self::MOBILE_SUFFIX ] ) ) {
					// A URL for mobile.
					$preload_urls[ $path . self::MOBILE_SUFFIX ] = [
						'url'    => $url,
						'mobile' => true,
						'source' => self::PRELOAD_ID,
					];
				}
			}
		}

		if ( ! $preload_urls ) {
			return;
		}

		array_map( [ $this->preload_process, 'push_to_queue' ], $preload_urls );

		set_transient( $this->get_running_transient_name(), $nbr_homes );
		$this->preload_process->save()->dispatch();
	}

	/**
	 * Gets links in the content of the URL provided.
	 *
	 * @since  3.2.2
	 * @since  3.5 $item is an array.
	 * @author Remy Perona
	 *
	 * @param  array $item {
	 *     The item to get content and links from: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return bool|array
	 */
	private function get_urls( array $item ) {
		$user_agent = $this->preload_process->get_item_user_agent( $item );

		/**
		 * Filters the arguments for the partial preload request.
		 *
		 * @since 3.2
		 * @author Remy Perona
		 *
		 * @param array $args Request arguments.
		 */
		$args = apply_filters(
			'rocket_homepage_preload_url_request_args',
			[
				'timeout'    => 10,
				'user-agent' => $user_agent,
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$response         = wp_remote_get( esc_url_raw( $item['url'] ), $args );
		$errors           = get_transient( 'rocket_preload_errors' );
		$errors           = is_array( $errors ) ? $errors : [];
		$errors['errors'] = isset( $errors['errors'] ) && is_array( $errors['errors'] ) ? $errors['errors'] : [];

		if ( is_wp_error( $response ) ) {
			// Translators: %1$s is an URL, %2$s is the error message, %3$s = opening link tag, %4$s = closing link tag.
			$errors['errors'][] = sprintf( __( 'Preload encountered an error. Could not gather links on %1$s because of the following error: %2$s. %3$sLearn more%4$s.', 'rocket' ), $item['url'], $response->get_error_message(), '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

			set_transient( 'rocket_preload_errors', $errors );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			switch ( $response_code ) {
				case 401:
				case 403:
					// Translators: %1$s is an URL, %2$s is the HTTP response code, %3$s = opening link tag, %4$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Preload encountered an error. %1$s is not accessible to due to the following response code: %2$s. Security measures could be preventing access. %3$sLearn more%4$s.', 'rocket' ), $item['url'], $response_code, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					set_transient( 'rocket_preload_errors', $errors );
					break;
				case 404:
					// Translators: %1$s is an URL, %2$s = opening link tag, %3$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Preload encountered an error. %1$s is not accessible to due to the following response code: 404. Please make sure your homepage is accessible in your browser. %2$sLearn more%3$s.', 'rocket' ), $item['url'], '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					set_transient( 'rocket_preload_errors', $errors );
					break;
				case 500:
					// Translators: %1$s is an URL, %2$s = opening link tag, %3$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Preload encountered an error. %1$s is not accessible to due to the following response code: 500. Please check with your web host about server access. %2$sLearn more%3$s.', 'rocket' ), $item['url'], '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					set_transient( 'rocket_preload_errors', $errors );
					break;
				default:
					// Translators: %1$s is an URL, %2$s is the HTTP response code, %3$s = opening link tag, %4$s = closing link tag.
					$errors['errors'][] = sprintf( __( 'Preload encountered an error. Could not gather links on %1$s because it returned the following response code: %2$s. %3$sLearn more%4$s.', 'rocket' ), $item['url'], $response_code, '<a href="https://docs.wp-rocket.me/article/1065-sitemap-preload-is-slow-or-some-pages-are-not-preloaded-at-all#failed-preload" rel="noopener noreferrer" target=_"blank">', '</a>' );

					set_transient( 'rocket_preload_errors', $errors );
					break;
			}

			return false;
		}

		$content = wp_remote_retrieve_body( $response );

		preg_match_all( '/<a\s+(?:[^>]+?[\s"\']|)href\s*=\s*(["\'])(?<href>[^"\']+)\1/imU', $content, $urls );

		return array_unique( $urls['href'] );
	}

	/**
	 * Checks if the URL should be preloaded.
	 *
	 * @since  3.2.2
	 * @access private
	 * @author Remy Perona
	 *
	 * @param  string $url URL to check.
	 * @param  string $home_url Homepage URL.
	 * @param  string $home_host Homepage host.
	 * @return bool
	 */
	private function should_preload( $url, $home_url, $home_host ) {
		$url = html_entity_decode( $url ); // & symbols in URLs are changed to &#038; when using WP Menu editor

		$url_data = get_rocket_parse_url( $url );

		if ( empty( $url_data ) ) {
			return false;
		}

		if ( ! empty( $url_data['fragment'] ) ) {
			return false;
		}

		if ( empty( $url_data['host'] ) ) {
			$url = home_url( $url );
		}

		$url = \rocket_add_url_protocol( $url );

		if ( untrailingslashit( $url ) === untrailingslashit( $home_url ) ) {
			return false;
		}

		if ( $home_host !== $url_data['host'] ) {
			return false;
		}

		if ( $this->is_file_url( $url ) ) {
			return false;
		}

		if ( ! empty( $url_data['path'] ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $url_data['path'] ) ) {
			return false;
		}

		$cache_query_strings = implode( '|', \get_rocket_cache_query_string() );

		if ( ! empty( $url_data['query'] ) && ! preg_match( '/(' . $cache_query_strings . ')/iU', $url_data['query'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if URL is an URL to a file.
	 *
	 * @since  3.2.2
	 * @access private
	 * @author Remy Perona
	 *
	 * @param  string $url URL to check.
	 * @return bool
	 */
	private function is_file_url( $url ) {
		/**
		 * Filters the list of files types to check when getting URLs on the homepage
		 *
		 * @since 3.2.2
		 * @author Remy Perona
		 *
		 * @param array $file_types Array of file extensions.
		 */
		$file_types = apply_filters(
			'rocket_preload_file_types',
			[
				'jpg',
				'jpeg',
				'jpe',
				'png',
				'gif',
				'webp',
				'bmp',
				'tiff',
				'mp3',
				'ogg',
				'mp4',
				'm4v',
				'avi',
				'mov',
				'flv',
				'swf',
				'webm',
				'pdf',
				'doc',
				'docx',
				'txt',
				'zip',
				'tar',
				'bz2',
				'tgz',
				'rar',
			]
		);

		$file_types = implode( '|', $file_types );

		if ( preg_match( '#\.(?:' . $file_types . ')$#iU', $url ) ) {
			return true;
		}

		return false;
	}
}
