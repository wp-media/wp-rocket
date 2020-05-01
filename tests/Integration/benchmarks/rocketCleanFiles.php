<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @group benchmarks
 * @group rocket_clean_files
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	private $stats = [];

	public function setUp() {
		if ( empty( $this->config ) ) {
			$this->config = $this->getConfigTestData();
		}

		parent::setUp();
	}

	/**
	 * @dataProvider benchmarkProvider
	 */
	public function testRegexIterator( $urls ) {
		$this->stats['number_urls'] = count( $urls );
		$start_time                 = $this->getTime();

		$this->rocket_clean_files( $urls );

		$this->stats['total'] = $this->getTime() - $start_time;
		$this->printResults();
		exit;
	}

	/**
	 * @dataProvider benchmarkProvider
	 * @group        original
	 */
	public function testGlob( $urls ) {
		$this->stats['number_urls'] = count( $urls );
		$start_time                 = $this->getTime();

		$this->rocket_clean_files_glob( $urls );

		$this->stats['total'] = $this->getTime() - $start_time;
		$this->printResults();
		exit;
	}

	private function printResults() {
		echo "\n Results for {$this->stats['number_urls']} URLS \n";
		echo "\n\n times shown in milliseconds (ms) \n";
		echo " URL \t\t\t\t| #Entries     | glob or regex | foreach | total \n";
		echo " ------------------------------------------------------------------\n";
		foreach ( $this->stats['urls'] as $url => $stats ) {
			printf( "%s \t| %-12s | %-16s | %-16s | %-16s \n",
				$url,
				$stats['#entries'],
				number_format( $stats['dirs'], 8 ),
				$stats['foreach'],
				number_format( $stats['total'], 8 ),
			);
		}
		echo " ------------------------------------------------------------------\n";
		printf( "\n\n Total time: %d seconds\n", number_format( $this->stats['total'], 8 ) );
	}

	private function rocket_clean_files( $urls ) {
		/**
		 * Filter URLs that the cache file to be deleted.
		 *
		 * @since 1.1.0
		 *
		 * @param array URLs that will be returned.
		 */
		$urls = (array) apply_filters( 'rocket_clean_files', $urls );
		$urls = array_filter( $urls );
		if ( empty( $urls ) ) {
			return;
		}

		$cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' );
		$iterator   = _rocket_get_cache_path_iterator( $cache_path );
		if ( false === $iterator ) {
			return;
		}

		$cache_path_regex = str_replace( '/', '\/', $cache_path );

		foreach ( $urls as $url ) {
			$num_entries = 0;
			$inner_start = $this->getTime();
			$entries     = _rocket_get_entries_regex( $iterator, $url, $cache_path_regex );
			$regex_time  = $this->getTime() - $inner_start;

			foreach ( $entries as $entry ) {
				rocket_rrmdir( $entry->getPathname() );
				$num_entries ++;
			}

			$done_time                   = $this->getTime();
			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $regex_time,
				'foreach'  => $done_time - $regex_time,
				'total'    => $done_time - $inner_start,
			];

			unset( $entries, $num_entries );
		}
	}

	private function rocket_clean_files_glob( $urls ) {
		$urls = apply_filters( 'rocket_clean_files', $urls );
		$urls = array_filter( (array) $urls );

		if ( ! $urls ) {
			return;
		}
		$cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' );

		foreach ( $urls as $url ) {
			$inner_start = $this->getTime();
			$dirs        = glob( $cache_path . rocket_remove_url_protocol( $url ), GLOB_NOSORT );
			$glob_time   = $this->getTime() - $inner_start;

			$num_entries = 0;
			if ( $dirs ) {
				$num_entries = count( $dirs );
				foreach ( $dirs as $dir ) {
					rocket_rrmdir( $dir );
				}
			}

			$done_time                   = $this->getTime();
			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $glob_time,
				'foreach'  => $done_time - $glob_time,
				'total'    => $done_time - $inner_start,
			];

			unset( $entries, $num_entries );
		}
	}

	private function getTime() {
		return microtime( true ); // * 1000;
	}

	public function benchmarkProvider() {
		if ( empty( $this->config ) ) {
			$this->config = $this->getConfigTestData();
		}

		return $this->config['test_data'];
	}

	protected function getConfigTestData() {
		$urls = [];
		for ( $post_id = 1; $post_id < 30; $post_id ++ ) {
			$urls[] = "http://example.org/post{$post_id}/";
		}

		return [
			// Use in tests when the test data starts in this directory.
			'vfs_dir'   => 'wp-content/cache/wp-rocket/',
			'structure' => require __DIR__ . '/large.php',
			'test_data' => [
				[
					'urls' => $urls,
				],
			],
		];
	}
}
