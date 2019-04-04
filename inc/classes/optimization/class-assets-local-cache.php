<?php
namespace WP_Rocket\Optimization;

/**
 * Cache locally 3rd party assets.
 *
 * @since 3.1
 * @author Remy Perona
 */
class Assets_Local_Cache {
	/**
	 * 3rd party assets cache folder path
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $cache_path;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $cache_path 3rd party assets cache folder path.
	 */
	public function __construct( $cache_path ) {
		$this->cache_path = $cache_path . '/3rd-party/';
	}

	/**
	 * Gets content for the provided URL.
	 * Use the local cache file if it exists, else get it from the 3rd party URL and save it locally for future use.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url URL to get the content from.
	 * @return string
	 */
	public function get_content( $url ) {
		$parts    = wp_parse_url( $url );
		$filename = $parts['host'] . str_replace( '/', '-', $parts['path'] );
		$filepath = $this->cache_path . $filename;

		if ( $this->local_cache_exists( $filepath ) ) {
			return $this->get_file_content( $filepath );
		}

		$content = $this->get_url_content( $url );

		if ( ! $content ) {
			return '';
		}

		$this->write_file( $content, $filepath );

		return $content;
	}

	/**
	 * Checks if the cache file for the specified asset exists.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $filepath Filepath of the file to check.
	 * @return bool
	 */
	protected function local_cache_exists( $filepath ) {
		return \rocket_direct_filesystem()->is_readable( $filepath );
	}

	/**
	 * Gets content from an URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url URL to get the content from.
	 * @return string
	 */
	protected function get_url_content( $url ) {
		$content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( ! $content ) {
			return false;
		}

		return $content;
	}

	/**
	 * Gets content of a file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $file File path.
	 * @return string
	 */
	protected function get_file_content( $file ) {
		return rocket_direct_filesystem()->get_contents( $file );
	}

	/**
	 * Writes the content to a file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content       Content to write.
	 * @param string $file          Path to the file to write in.
	 * @return bool
	 */
	protected function write_file( $content, $file ) {
		if ( rocket_direct_filesystem()->is_readable( $file ) ) {
			return true;
		}

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		return rocket_put_content( $file, $content );
	}
}
