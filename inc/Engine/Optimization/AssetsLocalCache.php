<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Filesystem_Direct;

/**
 * Cache locally 3rd party assets.
 *
 * @since 3.1
 */
class AssetsLocalCache {
	/**
	 * 3rd party assets cache folder path
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	protected $cache_path;

	/**
	 * Filesystem instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 *
	 * @param string               $cache_path 3rd party assets cache folder path.
	 * @param WP_Filesystem_Direct $filesystem Filesysten instance.
	 */
	public function __construct( $cache_path, $filesystem ) {
		$this->cache_path = "{$cache_path}3rd-party/";
		$this->filesystem = $filesystem;
	}

	/**
	 * Gets content for the provided URL.
	 * Use the local cache file if it exists, else get it from the 3rd party URL and save it locally for future use.
	 *
	 * @since 3.1
	 *
	 * @param string $url URL to get the content from.
	 * @return string
	 */
	public function get_content( $url ) {
		$filepath = $this->get_filepath( $url );

		if ( empty( $filepath ) ) {
			return '';
		}

		if ( $this->filesystem->is_readable( $filepath ) ) {
			return $this->filesystem->get_contents( $filepath );
		}

		$content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( empty( $content ) ) {
			return '';
		}

		$this->write_file( $content, $filepath );

		return $content;
	}

	/**
	 * Gets the filepath of the local copy for the given URL
	 *
	 * @since 3.7
	 *
	 * @param string $url URL to get filepath for.
	 * @return string
	 */
	public function get_filepath( $url ) {
		$parts = wp_parse_url( $url );

		if ( empty( $parts['path'] ) ) {
			return '';
		}

		$filename = $parts['host'] . str_replace( '/', '-', $parts['path'] );

		return $this->cache_path . $filename;
	}

	/**
	 * Writes the content to a file
	 *
	 * @since 3.1
	 *
	 * @param string $content Content to write.
	 * @param string $file    Path to the file to write in.
	 * @return bool
	 */
	protected function write_file( $content, $file ) {
		if ( $this->filesystem->is_readable( $file ) ) {
			return true;
		}

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		return rocket_put_content( $file, $content );
	}
}
