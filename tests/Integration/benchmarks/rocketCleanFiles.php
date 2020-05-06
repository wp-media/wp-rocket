<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

use Exception;
use RegexIterator;

/**
 * @group benchmarks
 * @group rocket_clean_files
 */
class Test_RocketCleanFiles extends TestCase {

	/**
	 * @group spl
	 */
	public function testRegexIterator() {
		$this->test_type = 'spl';
		$start_time      = $this->getTime();

		_rocket_get_cache_dirs( '', '', true );
		$this->rocket_clean_files( self::$urls );

		$this->stats['total'] = $this->getTime() - $start_time;

		// For the display only.
		$this->assertTrue( is_array( $this->stats ) );
	}

	/**
	 * @group  glob
	 */
	public function testGlob() {
		$this->test_type = 'glob';
		$start_time      = $this->getTime();

		$this->rocket_clean_files_glob( self::$urls, true );

		$this->stats['total'] = $this->getTime() - $start_time;

		// For the display only.
		$this->assertTrue( is_array( $this->stats ) );
	}

	private function rocket_clean_files( $urls, $filesystem = null ) {
		$urls = (array) $urls;
		if ( empty( $urls ) ) {
			return;
		}

		$urls = array_filter( $urls );
		if ( empty( $urls ) ) {
			return;
		}

		/** This filter is documented in inc/front/htaccess.php */
		$url_no_dots = (bool) apply_filters( 'rocket_url_no_dots', false );
		$cache_path  = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' );

		if ( empty( $filesystem ) ) {
			$filesystem = rocket_direct_filesystem();
		}

		/**
		 * Fires before all cache files are deleted.
		 *
		 * @since  3.2.2
		 *
		 * @param array $urls The URLs corresponding to the deleted cache files.
		 */
		do_action( 'before_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		foreach ( $urls as $url ) {
			$num_entries = 0;
			$inner_start = $this->getTime();

			/**
			 * Fires before the cache file is deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $url The URL that the cache file to be deleted.
			 */
			do_action( 'before_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

			if ( $url_no_dots ) {
				$url = str_replace( '.', '_', $url );
			}

			$parsed_url = get_rocket_parse_url( $url );

			foreach ( _rocket_get_cache_dirs( $parsed_url['host'], $cache_path ) as $dir ) {
				$entry = $dir . $parsed_url['path'];
				// Skip if the dir/file does not exist.
				if ( ! $filesystem->exists( $entry ) ) {
					continue;
				}

				$num_entries++;
				if ( $filesystem->is_dir( $entry ) ) {
					rocket_rrmdir( $entry, [], $filesystem );
				} else {
					$filesystem->delete( $entry );
				}
			}

			/**
			 * Fires after the cache file is deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $url The URL that the cache file was deleted.
			 */
			do_action( 'after_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => 0,
				'foreach'  => 0,
				'total'    => $done_time - $inner_start,
			];
		}

		/**
		 * Fires after all cache files are deleted.
		 *
		 * @since  3.2.2
		 *
		 * @param array $urls The URLs corresponding to the deleted cache files.
		 */
		do_action( 'after_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	private function rocket_clean_files_glob( $urls, $v352rrmdir = false ) {
		$urls = (array) $urls;

		/**
		 * Filter URLs that the cache file to be deleted.
		 *
		 * @since 1.1.0
		 *
		 * @param array URLs that will be returned.
		 */
		$urls = apply_filters( 'rocket_clean_files', $urls );
		$urls = array_filter( (array) $urls );

		if ( ! $urls ) {
			return;
		}

		/**
		 * Fires before all cache files are deleted.
		 *
		 * @since  3.2.2
		 * @author Grégory Viguier
		 *
		 * @param array $urls The URLs corresponding to the deleted cache files.
		 */
		do_action( 'before_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		foreach ( $urls as $url ) {
			$inner_start = $this->getTime();

			/**
			 * Fires before the cache file is deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $url The URL that the cache file to be deleted.
			 */
			do_action( 'before_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

			/** This filter is documented in inc/front/htaccess.php */
			if ( apply_filters( 'rocket_url_no_dots', false ) ) {
				$url = str_replace( '.', '_', $url );
			}

			$dirs        = glob( self::$cache_path . rocket_remove_url_protocol( $url ), GLOB_NOSORT );
			$glob_time   = $this->getTime();
			$num_entries = $dirs ? count( $dirs ) : 0;

			if ( $dirs ) {
				foreach ( $dirs as $dir ) {
					if ( $v352rrmdir ) {
						$this->rocket_rrmdir( $dir );
					} else {
						rocket_rrmdir( $dir );
					}
				}
			}

			/**
			 * Fires after the cache file is deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $url The URL that the cache file was deleted.
			 */
			do_action( 'after_rocket_clean_file', $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $glob_time - $inner_start,
				'foreach'  => $done_time - $glob_time,
				'total'    => $done_time - $inner_start,
			];
		}


		/**
		 * Fires after all cache files are deleted.
		 *
		 * @since  3.2.2
		 * @author Grégory Viguier
		 *
		 * @param array $urls The URLs corresponding to the deleted cache files.
		 */
		do_action( 'after_rocket_clean_files', $urls ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * WPR <= v3.5.2
	 */
	private function rocket_rrmdir( $dir, $dirs_to_preserve = [] ) {
		$dir = untrailingslashit( $dir );

		/**
		 * Fires before a file/directory cache is deleted
		 *
		 * @since 1.1.0
		 *
		 * @param string $dir File/Directory to delete.
		 * @param array $dirs_to_preserve Directories that should not be deleted.
		 */
		do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		// Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
		$nginx_mobile_detect_file = $dir . '/.mobile-active';

		if ( rocket_direct_filesystem()->is_dir( $dir ) && rocket_direct_filesystem()->exists( $nginx_mobile_detect_file ) ) {
			rocket_direct_filesystem()->delete( $nginx_mobile_detect_file );
		}

		// Remove the hidden empty file for webp.
		$nowebp_detect_file = $dir . '/.no-webp';

		if ( rocket_direct_filesystem()->is_dir( $dir ) && rocket_direct_filesystem()->exists( $nowebp_detect_file ) ) {
			rocket_direct_filesystem()->delete( $nowebp_detect_file );
		}

		if ( ! rocket_direct_filesystem()->is_dir( $dir ) ) {
			rocket_direct_filesystem()->delete( $dir );
			return;
		};

		$dirs = glob( $dir . '/*', GLOB_NOSORT );
		if ( $dirs ) {

			$keys = [];
			foreach ( $dirs_to_preserve as $dir_to_preserve ) {
				$matches = preg_grep( "#^$dir_to_preserve$#", $dirs );
				$keys[]  = reset( $matches );
			}

			$dirs = array_diff( $dirs, array_filter( $keys ) );
			foreach ( $dirs as $dir ) {
				if ( rocket_direct_filesystem()->is_dir( $dir ) ) {
					rocket_rrmdir( $dir, $dirs_to_preserve );
				} else {
					rocket_direct_filesystem()->delete( $dir );
				}
			}
		}

		rocket_direct_filesystem()->delete( $dir );

		/**
		 * Fires after a file/directory cache was deleted
		 *
		 * @since 1.1.0
		 *
		 * @param string $dir File/Directory to delete.
		 * @param array $dirs_to_preserve Dirs that should not be deleted.
		 */
		do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
}
