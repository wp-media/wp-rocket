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

		rocket_clean_files( self::$urls, self::$filesystem );

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

		rocket_clean_files_v352( self::$urls );

		$this->stats['total'] = $this->getTime() - $start_time;

		// For the display only.
		$this->assertTrue( is_array( $this->stats ) );
	}
}
