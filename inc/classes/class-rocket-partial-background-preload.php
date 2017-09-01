<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the automatic partial preload background process.
 *
 * @since 2.11
 *
 * @see WP_Background_Process
 */
class Rocket_Partial_Background_Preload extends WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'automatic_partial_preload';

	/**
	 * Counter for number of preloaded URLs.
	 *
	 * @since 2.11
	 * @access protected
	 * @var string Counter
	 */
	protected $count = 0;

	/**
	 * Preload the URL provided by $item
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return null
	 */
	protected function task( $item ) {
		if ( $this->is_already_cached( $item ) ) {
			return false;
		}

		/**
		 * Filters the arguments for the preload request
		 *
		 * @since 2.11
		 * @author Remy Perona
		 *
		 * @param array $args Arguments for the request.
		 */
		$args = apply_filters( 'rocket_preload_url_request_args',
			array(
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => 'wprocket-partial-preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
			)
		);

		wp_remote_get( esc_url_raw( $item ), $args );
		usleep( get_rocket_option( 'sitemap_preload_url_crawl', '500000' ) );
		$this->count++;

		return false;
	}

	/**
	 * Check if the cache file for $item already exists
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return bool true if exists, false otherwise
	 */
	protected function is_already_cached( $item ) {
		$host = ( isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : time();
		$host = trim( strtolower( $host ), '.' );
		$host = urlencode( $host );
		$host = isset( $rocket_url_no_dots ) ? str_replace( '.', '_', $host ) : $host;
		$file_cache_path = WP_ROCKET_CACHE_PATH . $host . '/' . $item . '/index.html';

		return file_exists( $file_cache_path );
	}

	/**
	 * Complete
	 */
	protected function complete() {
		set_transient( 'rocket_automatic_preload_complete', $this->count );
		parent::complete();
	}

}
