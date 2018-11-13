<?php
namespace WP_Rocket\Preload;

/**
 * Preloads the homepage and the internal URLs on it
 *
 * @since 3.2
 * @author Remy Perona
 */
class Homepage extends Abstract_Preload {
	/**
	 * Gets the internal URLs on the homepage and sends them to the preload queue
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $home_urls Homepages URLs to preload.
	 * @return void
	 */
	public function preload( $home_urls ) {
		foreach ( $home_urls as $home_url ) {
			$urls      = $this->get_urls( $home_url );
			$home_host = wp_parse_url( $home_url, PHP_URL_HOST );

			foreach ( $urls as $url ) {
				if ( ! $this->should_preload( $url, $home_url, $home_host ) ) {
					continue;
				}

				$this->preload_process->push_to_queue( $url );
			}
		}

		set_transient( 'rocket_preload_running', 1 );
		$this->preload_process->save()->dispatch();
	}

	/**
	 * Gets links in the content of the URL provided
	 *
	 * @since 3.2.2
	 * @author Remy Perona
	 *
	 * @param string $url URL to get content and links from.
	 * @return array
	 */
	private function get_urls( $url ) {
		// This filter is documented in inc/classes/preload/class-partial-process.php.
		$args = apply_filters(
			'rocket_partial_preload_url_request_args',
			[
				'user-agent' => 'WP Rocket/Partial_Preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', true ), // WPCS: prefix ok.
			]
		);

		$response = wp_remote_get( $url, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$content = wp_remote_retrieve_body( $response );

		preg_match_all( '/<a\s+(?:[^>]+?[\s"\']|)href\s*=\s*(["\'])(?<href>[^"\']+)\1/imU', $content, $urls );

		return array_unique( $urls['href'] );
	}

	/**
	 * Checks if the URL should be preloaded
	 *
	 * @since 3.2.2
	 * @author Remy Perona
	 *
	 * @param string $url URL to check.
	 * @param string $home_url Homepage URL.
	 * @param string $home_host Homepage host.
	 * @return bool
	 */
	private function should_preload( $url, $home_url, $home_host ) {
		$url = html_entity_decode( $url ); // & symbols in URLs are changed to &#038; when using WP Menu editor

		$url_data = wp_parse_url( $url );

		if ( empty( $url_data ) ) {
			return false;
		}

		if ( isset( $url_data['fragment'] ) ) {
			return false;
		}

		if ( empty( $url_data['host'] ) ) {
			$url = home_url( $url );
		}

		$url = \rocket_add_url_protocol( $url );

		if ( $url === $home_url ) {
			return false;
		}

		if ( $home_host !== $url_data['host'] ) {
			return false;
		}

		if ( $this->is_file_url( $url ) ) {
			return false;
		}

		if ( preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $url_data['path'] ) ) {
			return false;
		}

		$cache_query_strings = implode( '|', \get_rocket_cache_query_string() );

		if ( ! empty( $url_data['query'] ) && ! preg_match( '/(' . $cache_query_strings . ')/iU', $url_data['query'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if URL is an URL to a file
	 *
	 * @since 3.2.2
	 * @author Remy Perona
	 *
	 * @param string $url URL to check.
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

		if ( preg_match( '#\.' . $file_types . '$#iU', $url ) ) {
			return true;
		}

		return false;
	}
}
