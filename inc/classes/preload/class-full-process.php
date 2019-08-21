<?php
namespace WP_Rocket\Preload;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the preload background process.
 *
 * @since 3.2
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class Full_Process extends \WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $action = 'preload';

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
		$count = get_transient( 'rocket_preload_running' );
		set_transient( 'rocket_preload_running', $count + 1 );

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
		$args = apply_filters( 'rocket_preload_url_request_args', [
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'WP Rocket/Preload',
			'sslverify'  => apply_filters( 'https_local_ssl_verify', false ),
		] );

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

	/**
	 * Updates transients on complete
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function complete() {
		set_transient( 'rocket_preload_complete', get_transient( 'rocket_preload_running' ) );
		set_transient( 'rocket_preload_complete_time', date_i18n( "F j, Y @ G:i", time() ) );
		delete_transient( 'rocket_preload_running' );
		parent::complete();
	}

	/**
	 * Checks if a process is already running
	 *
	 * @since 3.2.1.1
	 * @author Remy Perona
	 *
	 * @see WP_Background_Process::is_process_running()
	 * @return boolean
	 */
	public function is_process_running() {
		return parent::is_process_running();
	}
}

