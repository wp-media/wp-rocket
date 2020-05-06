<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

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

		$this->rocket_clean_files_glob( self::$urls );

		$this->stats['total'] = $this->getTime() - $start_time;

		// For the display only.
		$this->assertTrue( is_array( $this->stats ) );
	}

	private function rocket_clean_files( $urls ) {
		$urls = (array) apply_filters( 'rocket_clean_files', $urls );
		$urls = array_filter( $urls );
		if ( empty( $urls ) ) {
			return;
		}

		foreach ( $urls as $url ) {
			$num_entries = 0;
			$inner_start = $this->getTime();
			$parsed_url  = get_rocket_parse_url( $url );

			foreach ( $this->getCacheRootDirs( $parsed_url['host'] ) as $dir ) {
				$entry = $dir . $parsed_url['path'];
				// Skip if the dir/file does not exist.
				if ( ! self::$filesystem->exists( $entry ) ) {
					continue;
				}

				$num_entries ++;
				if ( self::$filesystem->is_dir( $entry ) ) {
					rocket_rrmdir( $entry, [], self::$filesystem );
				} else {
					self::$filesystem->delete( $entry );
				}
			}

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => 0,
				'foreach'  => 0,
				'total'    => $done_time - $inner_start,
			];
		}
	}

	private function getCacheRootDirs( $url_host ) {
		$iterator = _rocket_get_cache_path_iterator( self::$cache_path );
		if ( false === $iterator ) {
			return [];
		}

		$cache_path = basename( rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) . '/wp-rocket/';
		$cache_path = str_replace( '/', '\/', $cache_path );
		$regex      = sprintf( '/%s%s(.*)/i', $cache_path, $url_host );
		$iterator->setMaxDepth( 0 );

		try {
			$entries = RegexIterator( $iterator, $regex );
		} catch ( Exception $e ) {
			return [];
		}

		$dirs = [];
		foreach ( $entries as $entry ) {
			$dirs[] = $entry->getPathname();
		}

		return $dirs;
	}

	private function rocket_clean_files_glob( $urls ) {
		$urls = apply_filters( 'rocket_clean_files', $urls );
		$urls = array_filter( (array) $urls );
		if ( ! $urls ) {
			return;
		}

		foreach ( $urls as $url ) {
			$inner_start = $this->getTime();

			$dirs        = glob( self::$cache_path . rocket_remove_url_protocol( $url ), GLOB_NOSORT );
			$glob_time   = $this->getTime();
			$num_entries = $dirs ? count( $dirs ) : 0;

			if ( $dirs ) {
				foreach ( $dirs as $dir ) {
					rocket_rrmdir( $dir );
				}
			}

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $glob_time - $inner_start,
				'foreach'  => $done_time - $glob_time,
				'total'    => $done_time - $inner_start,
			];

			unset( $entries, $num_entries );
		}
	}
}
