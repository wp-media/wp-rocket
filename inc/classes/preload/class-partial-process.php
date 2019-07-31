<?php
namespace WP_Rocket\Preload;

/**
 * Extends the background process class for the partial preload background process.
 *
 * @since 3.2
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class Partial_Process extends \WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @since 3.2
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for partial preload
	 *
	 * @since 3.2
	 * @var string
	 */
	protected $action = 'partial_preload';

	/**
	 * Preload the URL provided by $item
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param mixed $item Queue item to iterate over.
	 * @return null
	 */
	protected function task( $item ) {
		if ( $this->is_already_cached( $item ) ) {
			return false;
		}

		/**
		 * Filters the arguments for the partial preload request
		 *
		 * @since 3.2
		 * @author Remy Perona
		 *
		 * @param array $args Request arguments.
		 */
		$args = apply_filters(
			'rocket_partial_preload_url_request_args',
			[
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => 'WP Rocket/Partial_Preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		wp_remote_get( esc_url_raw( $item ), $args );

		usleep( absint( get_rocket_option( 'sitemap_preload_url_crawl', 500000 ) ) );

		return false;
	}

	/**
	 * Check if the cache file for $item already exists
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param string $item Queue item to iterate over.
	 * @return bool
	 */
	protected function is_already_cached( $item ) {
		static $https;

		if ( ! isset( $https ) ) {
			$https = ( is_ssl() && get_rocket_option( 'cache_ssl' ) ) ? '-https' : '';
		}

		$url = get_rocket_parse_url( $item );

		/** This filter is documented in inc/functions/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$url['host'] = str_replace( '.', '_', $url['host'] );
		}

		$url['path'] = trailingslashit( $url['path'] );

		if ( '' !== $url['query'] ) {
			$url['query'] = '#' . $url['query'] . '/';
		}

		$file_cache_path = WP_ROCKET_CACHE_PATH . $url['host'] . strtolower( $url['path'] . $url['query'] ) . 'index' . $https . '.html';

		return rocket_direct_filesystem()->exists( $file_cache_path );
	}
}
