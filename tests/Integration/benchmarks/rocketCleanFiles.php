<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @group benchmarks
 * @group rocket_clean_files
 */
class Test_RocketCleanFiles extends TestCase {
	private $cache_path;
	private $filesystem;
	private $results_base_file;
	private $stats = [];
	private $urls  = [];

	public function setUp() {
		parent::setUp();

		define( 'FS_CHMOD_DIR', 0755 );
		define( 'FS_CHMOD_FILE', 0644 );
		$this->filesystem        = rocket_direct_filesystem();
		$this->cache_path        = __DIR__ . '/cache/wp-rocket/';
		$this->results_base_file = __DIR__ . '/results/rocket_clean_files_glob.txt';

		$this->createCache();
	}

	private function createCache() {
		$this->urls = [];
		$parent_dir = "{$this->cache_path}example.org/";

		for ( $i = 1; $i <= 30; $i ++ ) {
			$this->urls[] = "http://example.org/post{$i}/";

			$this->buildPostDir( $parent_dir, $i );

			$user_dir = "{$this->cache_path}example.org-user{$i}-123456/";
			for ( $j = 1; $j <= 30; $j ++ ) {
				$this->buildPostDir( $user_dir, $j );
			}
		}
	}

	private function buildPostDir( $parent_dir, $id ) {
		$post_dir = "{$parent_dir}post{$id}/";
		$this->filesystem->mkdir( $post_dir );
		$this->filesystem->copy( $parent_dir . 'index.html', $post_dir . 'index.html' );
		$this->filesystem->copy( $parent_dir . 'index.html_gzip', $post_dir . 'index.html_gzip' );
	}

	public function testRegexIterator() {
		$this->stats['number_urls'] = count( $this->urls );
		$start_time                 = $this->getTime();

		$this->rocket_clean_files( $this->urls );

		$this->stats['total'] = $this->getTime() - $start_time;
		$this->printResults();
		exit;
	}

	/**
	 * @group  glob
	 */
	public function testGlob() {
		$this->stats['number_urls'] = count( $this->urls );
		$start_time                 = $this->getTime();

		$this->rocket_clean_files_glob( $this->urls );

		$this->stats['total'] = $this->getTime() - $start_time;
		$this->printResults();

		// Save results for comparisons.
		$this->filesystem->put_contents( $this->results_base_file, json_encode( $this->stats ) );
		exit;
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

		$iterator = _rocket_get_cache_path_iterator( $this->cache_path );
		if ( false === $iterator ) {
			return;
		}

		$cache_path_regex = str_replace( '/', '\/', $this->cache_path );

		foreach ( $urls as $url ) {
			$num_entries = 0;
			$inner_start = $this->getTime();
			$entries     = _rocket_get_entries_regex( $iterator, $url, $cache_path_regex );
			$regex_time  = $this->getTime();

			foreach ( $entries as $entry ) {
				rocket_rrmdir( $entry->getPathname() );
				$num_entries++;
			}

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $regex_time - $inner_start,
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

		foreach ( $urls as $url ) {
			$inner_start = $this->getTime();

			$dirs = glob( $this->cache_path . rocket_remove_url_protocol( $url ), GLOB_NOSORT );

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

	private function getTime() {
		return microtime( true ) * 1000;
	}

	private function printResults() {
		echo "\n Results for {$this->stats['number_urls']} URLS \n";

		echo "\n\n times shown in milliseconds (ms) \n";
		echo " URL \t\t\t\t| #Entries | glob/spl   | foreach    | total \n";
		echo "     \t\t\t\t| (ms)     | (ms)       | (ms)       | (ms) \n";
		echo " -----------------------------------------------------------------------------------\n";
		foreach ( $this->stats['urls'] as $url => $stats ) {
			printf( "%s \t| %-8s | %-9s | %-9s | %-s \n",
				$url,
				$stats['#entries'],
				number_format( $stats['dirs'], 2 ),
				number_format( $stats['foreach'], 2 ),
				number_format( $stats['total'], 2 ),
			);
		}
		echo " -----------------------------------------------------------------------------------\n";

		printf( "\n\n Total time: %f seconds\n\n\n", number_format( $this->stats['total'] / 1000, 8 ) );
	}
}
