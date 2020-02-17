<?php
namespace WP_Rocket\Preload;

/**
 * Abstract preload class
 *
 * @since 3.2
 * @author Remy Perona
 */
abstract class Abstract_Preload {

	/**
	 * Suffix used to identify "mobile items" to preload.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @var string
	 */
	const MOBILE_SUFFIX = '##wpm-mobile##';

	/**
	 * Background Process instance
	 *
	 * @since 3.2
	 * @var Full_Process
	 */
	protected $preload_process;

	/**
	 * Cache processing that use get_rocket_cache_query_string().
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @var array
	 */
	protected $cache_query_strings;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Full_Process $preload_process Background Process instance.
	 */
	public function __construct( Full_Process $preload_process ) {
		$this->preload_process = $preload_process;
	}

	/**
	 * Cancels any preload process running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function cancel_preload() {
		delete_transient( 'rocket_preload_running' );

		if ( \method_exists( $this->preload_process, 'cancel_process' ) ) {
			$this->preload_process->cancel_process();
		}
	}

	/**
	 * Checks if a process is already running
	 *
	 * @since 3.2.1.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	public function is_process_running() {
		return $this->preload_process->is_process_running();
	}

	/**
	 * Create a unique identifier for a given URL.
	 * This is used for the "mobile items"
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param  string $url A URL.
	 * @return string
	 */
	protected function get_url_identifier( $url ) {
		if ( ! isset( $this->cache_query_strings ) ) {
			$this->cache_query_strings = array_fill_keys( get_rocket_cache_query_string(), '' );

			ksort( $this->cache_query_strings );
		}

		$path  = (array) wp_parse_url( $url );
		$query = isset( $path['query'] ) ? $path['query'] : '';
		$path  = isset( $path['path'] ) ? $path['path'] : '';
		$path  = strtolower( trailingslashit( $path ) );

		if ( ! $this->cache_query_strings ) {
			return $path;
		}

		parse_str( $query, $query_array );

		$query_array = array_intersect_key( $query_array, $this->cache_query_strings );
		$query_array = array_merge( $this->cache_query_strings, $query_array );

		return $path . '?' . http_build_query( $query_array );
	}
}
