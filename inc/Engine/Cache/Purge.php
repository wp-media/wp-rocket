<?php

namespace WP_Rocket\Engine\Cache;

use DirectoryIterator;
use Exception;

/**
 * Cache Purge handling class
 */
class Purge {
	/**
	 * Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Initialize the class
	 *
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 */
	public function __construct( $filesystem ) {
		$this->filesystem = $filesystem;
	}

	/**
	 * Purges cache for the dates archives of a post
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function purge_dates_archives( $post ) {
		global $wp_rewrite;

		foreach ( $this->get_dates_archives( $post ) as $url ) {
			$parsed_url = $this->parse_url( $url );

			foreach ( _rocket_get_cache_dirs( $parsed_url['host'] ) as $dir ) {
				$path = $dir . $parsed_url['path'];

				if ( ! $this->filesystem->exists( $path ) ) {
					continue;
				}

				foreach ( $this->get_iterator( $path ) as $item ) {
					if ( $item->isFile() ) {
						$this->filesystem->delete( $item->getPathname() );
					}
				}

				$this->maybe_remove_dir( $path . DIRECTORY_SEPARATOR . $wp_rewrite->pagination_base );
			}
		}
	}

	/**
	 * Gets the dates archives URLs for the provided post
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	private function get_dates_archives( $post ) {
		$time = get_the_time( 'Y-m-d', $post );

		if ( empty( $time ) ) {
			return [];
		}

		$date = explode( '-', $time );
		$urls = [
			get_year_link( $date[0] ),
			get_month_link( $date[0], $date[1] ),
			get_day_link( $date[0], $date[1], $date[2] ),
		];

		/**
		 * Filter the list of dates URLs.
		 *
		 * @since 1.1.0
		 *
		 * @param array $urls List of dates URLs.
		*/
		return (array) apply_filters( 'rocket_post_dates_urls', $urls );
	}

	/**
	 * Parses URL and return the parts array
	 *
	 * @since 3.6.1
	 *
	 * @param string $url URL to parse.
	 * @return array
	 */
	private function parse_url( $url ) {
		$parsed_url = get_rocket_parse_url( $url );

		/** This filter is documented in inc/front/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$parsed_url['host'] = str_replace( '.', '_', $parsed_url['host'] );
		}

		return $parsed_url;
	}

	/**
	 * Gets the iterator for the given path
	 *
	 * @since 3.6.1
	 *
	 * @param string $path Absolute path.
	 * @return DirectoryIterator|array
	 */
	private function get_iterator( $path ) {
		try {
			$iterator = new DirectoryIterator( $path );
		} catch ( Exception $e ) {
			// No action required, as logging not enabled.
			$iterator = [];
		}

		return $iterator;
	}

	/**
	 * Recursively remove the provided directory and its content
	 *
	 * @since 3.6.1
	 *
	 * @param string $dir Absolute path for the directory.
	 * @return void
	 */
	private function maybe_remove_dir( $dir ) {
		if ( $this->filesystem->is_dir( $dir ) ) {
			rocket_rrmdir( $dir, [], $this->filesystem );
		}
	}
}
