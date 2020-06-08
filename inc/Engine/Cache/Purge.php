<?php

namespace WP_Rocket\Engine\Cache;

use DirectoryIterator;
use Exception;

class Purge {
	private $filesystem;

	public function __construct( $filesystem ) {
		$this->filesystem = $filesystem;
	}

	public function purge_dates_archives( $post ) {
		global $wp_rewrite;

		foreach ( $this->get_dates_archives( $post ) as $url ) {
			$parsed_url = get_rocket_parse_url( $url );

			/** This filter is documented in inc/front/htaccess.php */
			if ( apply_filters( 'rocket_url_no_dots', false ) ) {
				$parsed_url['host'] = str_replace( '.', '_', $parsed_url['host'] );
			}

			foreach ( _rocket_get_cache_dirs( $parsed_url['host'] ) as $dir ) {
				$path = $dir . $parsed_url['path'];

				if ( ! $this->filesystem->exists( $path ) ) {
					continue;
				}

				try {
					$iterator = new DirectoryIterator( $path );
				} catch ( Exception $e ) {
					// No action required, as logging not enabled.
					$iterator = [];
				}

				foreach ( $iterator as $item ) {
					if ( $item->isFile() ) {
						$this->filesystem->delete( $item->getPathname() );
					}
				}

				$pagination_dir = $path . DIRECTORY_SEPARATOR . $wp_rewrite->pagination_base;

				if ( $this->filesystem->is_dir( $pagination_dir ) ) {
					rocket_rrmdir( $pagination_dir, [], $this->filesystem );
				}
			}
		}
	}

	private function get_dates_archives( $post ) {
		$time = get_the_time( 'Y-m-d', $post );

		if ( empty( $time ) ) {
			return [];
		}

		$date  = explode( '-', $time );
		$urls  = [
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
}
