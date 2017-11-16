<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the sitemap preload background process.
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Rocket_Sitemap_Preload_Process extends WP_Background_Process {
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

	/**
	 * Count the number of preloaded URLs.
	 *
	 * @access protected
	 * @var int $count Number of preloaded URLs.
	 */
	protected $count = 0;

	/**
	 * Dispatch
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function dispatch() {
		set_transient( 'rocket_sitemap_preload_process', 'running' );

		// Perform remote post.
		return parent::dispatch();
	}

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

		$args = array(
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'wprocketbot',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
		);

		$response = wp_remote_get( esc_url_raw( $item ), $args );

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$this->count++;
		}

		usleep( get_rocket_option( 'sitemap_preload_url_crawl', '500000' ) );

		return false;
	}

	/**
	 * Check if the cache file for $item already exists
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $item Queue item to iterate over.
	 * @return bool true if exists, false otherwise
	 */
	protected function is_already_cached( $item ) {
		$url = get_rocket_parse_url( $item );

		/** This filter is documented in inc/front/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$url['host'] = str_replace( '.', '_', $url['host'] );
		}

		$file_cache_path = WP_ROCKET_CACHE_PATH . $url['host'] . '/' . strtolower( $url['path'] ) . '/index.html';

		return file_exists( $file_cache_path );
	}

	/**
	 * Complete
	 */
	protected function complete() {
		delete_transient( 'rocket_sitemap_preload_process' );
		set_transient( 'rocket_sitemap_preload_complete', $this->count );
		parent::complete();
	}

}

$GLOBALS['rocket_sitemap_background_process'] = new Rocket_Sitemap_Preload_Process();
