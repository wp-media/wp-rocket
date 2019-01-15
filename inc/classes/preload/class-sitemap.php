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

		$urls_group = array_filter( $urls_group );

		if ( ! $urls_group ) {
			return;
		}

		foreach ( $urls_group as $urls ) {
			$urls = array_flip( array_flip( $urls ) );
			foreach ( $urls as $url ) {
				$path = wp_parse_url( $url, PHP_URL_PATH );

				if ( isset( $path ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $path ) ) {
					continue;
				}

				$this->preload_process->push_to_queue( $url );
			}
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
				'user-agent' => 'WP Rocket/Sitemaps',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', true ), // WPCS: prefix ok.
			]
		);

		$sitemap          = wp_remote_get( esc_url( $sitemap_url ), $args );
		$errors           = get_transient( 'rocket_preload_errors' );
		$errors           = is_array( $errors ) ? $errors : [];
		$errors['errors'] = isset( $errors['errors'] ) && is_array( $errors['errors'] ) ? $errors['errors'] : [];

		if ( is_wp_error( $sitemap ) ) {
			// Translators: %1$s is a XML sitemap URL, %2$s is the error message.
			$errors['errors'][] = sprintf( __( 'Could not gather links from %1$s because of the following error: %2$s', 'rocket' ), $sitemap_url, $sitemap->get_error_message() );

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		if ( 200 !== wp_remote_retrieve_response_code( $sitemap ) ) {
			// Translators: %1$s is an URL, %2$s is the HTTP response code.
			$errors['errors'][] = sprintf( __( 'Could not gather links on %1$s because it returned the following response code: %2$s', 'rocket' ), $sitemap_url, wp_remote_retrieve_response_code( $sitemap ) );

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		$xml_data = wp_remote_retrieve_body( $sitemap );

		if ( empty( $xml_data ) ) {
			// Translators: %1$s is a XML sitemap URL.
			$errors['errors'][] = sprintf( __( 'Could not gather links from %1$s because the file is empty.', 'rocket' ), $sitemap_url );

			set_transient( 'rocket_preload_errors', $errors );
			return [];
		}

		libxml_use_internal_errors( true );

		$xml = simplexml_load_string( $xml_data );

		if ( false === $xml ) {
			$errors['errors'][] = sprintf(
				// Translators: %1$s is a XML sitemap URL.
				__( 'Could not gather links from %1$s because of an error during the XML sitemap parsing.', 'rocket' ),
				$sitemap_url
			);

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
}
