<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @group benchmarks
 * @group rocket_clean_files
 */
class Test_RocketCleanFiles extends TestCase {
	private static $cache_path;
	private static $filesystem;
	private static $results_base_file;
	private static $results_path;
	private static $results_id;
	private static $urls  = [];
	private        $stats = [];

	private $test_type;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', 0755 );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', 0644 );
		}

		self::$urls = [];
		for ( $i = 1; $i <= 30; $i ++ ) {
			self::$urls[] = "http://example.org/post{$i}/";
		}

		self::$filesystem        = rocket_direct_filesystem();
		self::$cache_path        = __DIR__ . '/cache/wp-rocket/';
		self::$results_path      = __DIR__ . '/results/';
		self::$results_base_file = self::$results_path . 'rocket_clean_files_glob.txt';
		self::$results_id        = date( 'YmdHms' );
	}

	public function setUp() {
		parent::setUp();

		$this->createCache();

		$this->stats = [
			'number_urls' => count( self::$urls ),
			'total'       => 0,
			'avgs'        => [
				'dirs'    => 0,
				'foreach' => 0,
				'total'   => 0,
			],
			'urls'        => [],
		];
	}

	private function createCache() {
		$parent_dir = self::$cache_path . 'example.org/';

		for ( $i = 1; $i <= 30; $i ++ ) {
			$this->buildPostDir( $parent_dir, $i );

			$user_dir = self::$cache_path . "example.org-user{$i}-123456/";
			for ( $j = 1; $j <= 30; $j ++ ) {
				$this->buildPostDir( $user_dir, $j );
			}
		}
	}

	private function buildPostDir( $parent_dir, $id ) {
		$post_dir = "{$parent_dir}post{$id}/";
		self::$filesystem->mkdir( $post_dir );
		self::$filesystem->copy( $parent_dir . 'index.html', $post_dir . 'index.html' );
		self::$filesystem->copy( $parent_dir . 'index.html_gzip', $post_dir . 'index.html_gzip' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->prepResults();
		$this->printResults();
		$this->saveResults();
	}

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

		$iterator = _rocket_get_cache_path_iterator( self::$cache_path );
		if ( false === $iterator ) {
			return;
		}

		$cache_path_regex = str_replace( '/', '\/', self::$cache_path );

		foreach ( $urls as $url ) {
			$num_entries = 0;
			$inner_start = $this->getTime();
			$entries     = _rocket_get_entries_regex( $iterator, $url, $cache_path_regex );
			$regex_time  = $this->getTime();

			foreach ( $entries as $entry ) {
				rocket_rrmdir( $entry->getPathname() );
				$num_entries ++;
			}

			$done_time = $this->getTime();

			$this->stats['urls'][ $url ] = [
				'#entries' => $num_entries,
				'dirs'     => $regex_time - $inner_start,
				'foreach'  => $done_time - $regex_time,
				'total'    => $done_time - $inner_start,
			];
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

			$dirs = glob( self::$cache_path . rocket_remove_url_protocol( $url ), GLOB_NOSORT );

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
		echo "\n==================== \n";
		echo "Summary for {$this->test_type} \n";
		echo "==================== \n\n";
		echo "\t👉 Number of URLs: {$this->stats['number_urls']} \n";
		printf( "\t👉 Total time:     %f seconds\n", $this->stats['total'] );
		echo "\n\n\n";

		echo "-----------------------------------------------------------------------------------\n";
		echo " URL \t\t\t\t| #Entries | glob/spl   | foreach    | total \n";
		echo "     \t\t\t\t| (ms)     | (ms)       | (ms)       | (ms) \n";
		echo "-----------------------------------------------------------------------------------\n";

		foreach ( $this->stats['urls'] as $url => $stats ) {
			printf( "%s \t| %-8s | %-10s | %-9s | %-s \n", $url, $stats['#entries'], $stats['dirs'], $stats['foreach'], $stats['total'] );
		}

		echo "-----------------------------------------------------------------------------------\n";
		printf( " Average \t\t\t|          |  %-9s | %-9s | %-s \n", $this->stats['avgs']['dirs'], $this->stats['avgs']['foreach'], $this->stats['avgs']['total'] );
		echo "-----------------------------------------------------------------------------------\n";

		echo "\nNotes:\n";
		echo "\t1. URL individual times shown in milliseconds (ms) \n";
		echo "\t2. To convert to seconds, ms / 1000 \n\n\n";
	}

	private function saveResults() {
		$filename = sprintf( '%stest-%s-%s.csv', self::$results_path, $this->test_type, self::$results_id );

		try {
			$fp = fopen( $filename, 'w' );

			fputcsv( $fp, [ 'Number of URLs', $this->stats['number_urls'] ] );
			fputcsv( $fp, [ 'Total time (secs)', $this->stats['total'] ] );

			foreach ( $this->stats['urls'] as $url => $stats ) {
				fputcsv( $fp, [ $url, $stats['#entries'], $stats['dirs'], $stats['foreach'], $stats['total']  ] );
			}

			fputcsv( $fp, [
				'Averages',
				'',
				$this->stats['avgs']['dirs'],
				$this->stats['avgs']['foreach'],
				$this->stats['avgs']['total'],
			] );

		} finally {
			fclose( $fp );
		}
	}

	private function prepResults() {
		$keys = array_keys( $this->stats['avgs'] );
		foreach ( $this->stats['urls'] as $url => $stats ) {
			foreach ( $keys as $key ) {
				$stat                                = $stats[ $key ];
				$this->stats['avgs'][ $key ]         += $stat;
				$this->stats['urls'][ $url ][ $key ] = $this->formatStat( $stat );
			}
		}

		foreach ( $this->stats['avgs'] as $type => $total ) {
			$this->stats['avgs'][ $type ] = $this->formatStat( $total / $this->stats['number_urls'] );
		}

		$this->stats['total'] = $this->formatStat( ( $this->stats['total'] / 1000 ), 8 );
	}

	private function formatStat( $value, $decimals = 2 ) {
		return number_format( $value, $decimals );
	}
}
