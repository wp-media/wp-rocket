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
	 * Dispatch
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function dispatch() {

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

		/**
		 * Filters the arguments for the preload request
		 *
		 * @since 2.10.8
		 * @author Remy Perona
		 *
		 * @param array $args Arguments for the request.
		 */
		$args = apply_filters( 'rocket_preload_url_request_args', array(
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'WP Rocket/Preload',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
		) );

		$response = wp_remote_get( esc_url_raw( $item ), $args );

		$count = get_transient( 'rocket_sitemap_preload_running' );
		set_transient( 'rocket_sitemap_preload_running', $count + 1 );

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

		return rocket_direct_filesystem()->exists( $file_cache_path );
	}

	/**
	 * Complete
	 */
	protected function complete() {
		set_transient( 'rocket_sitemap_preload_complete', get_transient( 'rocket_sitemap_preload_running' ) );
		delete_transient( 'rocket_sitemap_preload_running' );
		parent::complete();
	}

}

$GLOBALS['rocket_sitemap_background_process'] = new Rocket_Sitemap_Preload_Process();
