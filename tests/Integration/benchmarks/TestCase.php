<?php

namespace WP_Rocket\Tests\Integration\benchmarks;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $cache_path;
	protected static $filesystem;
	protected static $results_base_file;
	protected static $results_path;
	protected static $results_id;
	protected static $urls = [];
	protected $stats = [];
	protected $test_type;

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
//			self::$urls[] = "http://example.org/child-{$i}/";
			self::$urls[] = "http://example.org/child-{$i}/grandchild-1/";
			self::$urls[] = "http://example.org/child-{$i}/grandchild-2/index.html_gzip";
			self::$urls[] = "http://example.org/child-{$i}/";
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

	protected function createCache() {
		$parent_dir = self::$cache_path . 'example.org/';

		for ( $i = 1; $i <= 30; $i ++ ) {
			// Do the main domain first.
			$this->buildCacheDir( $parent_dir, $i );

			// Now do each users' cache.
			$user_dir = self::$cache_path . "example.org-user{$i}-123456/";
			for ( $user_id = 1; $user_id <= 30; $user_id ++ ) {
				$this->buildCacheDir( $user_dir, $user_id );
			}
		}
	}

	protected function buildCacheDir( $parent_dir, $id ) {
		$this->buildDir( $parent_dir, "child-{$id}" );

		$sub_dir = "{$parent_dir}child-{$id}/";
		$this->buildDir( $sub_dir, 'grandchild-1' );
		$this->buildDir( $sub_dir, 'grandchild-2' );
	}

	protected function buildDir( $parent_dir, $id ) {
		$post_dir = "{$parent_dir}{$id}/";
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

	protected function getTime() {
		return microtime( true ) * 1000;
	}

	protected function printResults() {
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

	protected function saveResults() {
		$filename = sprintf( '%stest-%s-%s.csv', self::$results_path, $this->test_type, self::$results_id );

		try {
			$fp = fopen( $filename, 'w' );

			fputcsv( $fp, [ 'Number of URLs', $this->stats['number_urls'] ] );
			fputcsv( $fp, [ 'Total time (secs)', $this->stats['total'] ] );

			foreach ( $this->stats['urls'] as $url => $stats ) {
				fputcsv( $fp, [ $url, $stats['#entries'], $stats['dirs'], $stats['foreach'], $stats['total'] ] );
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

	protected function prepResults() {
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

	protected function formatStat( $value, $decimals = 2 ) {
		return number_format( $value, $decimals );
	}
}
