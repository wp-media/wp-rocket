<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the sitemap preload background process.
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Rocket_Sitemap_Background_Preload extends WP_Background_Process {
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
	protected $action = 'sitemap_preload';

	protected $count = 0;

	/**
	 * Preload the URL provided by $item
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return boolean false
	 */
	protected function task( $item ) {
		if ( $this->is_already_cached( $item ) ) {
			return false;
		}

		/**
		 * Filters the arguments for the preload request
		 *
		 * @since
		 * @author Remy Perona
		 *
		 * @param array $args Arguments for the request
		 */
		$args = apply_filters( 'rocket_sitemap_preload_request_args', array(
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'wprocketbot',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
		) );

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
		$host = str_replace( array( '..', chr( 0 ) ), '', $host );
		$host = isset( $rocket_url_no_dots ) ? str_replace( '.', '_', $host ) : $host;
		$path = parse_url( $item, PHP_URL_PATH );
		$file_cache_path = WP_ROCKET_CACHE_PATH . $host . '/' . $item . '/index.html';

		return file_exists( $file_cache_path );
	}

	/**
	 * Complete
	 */
	protected function complete() {
		set_transient( 'rocket_sitemap_preload_complete', $this->count );
		parent::complete();
	}
}
