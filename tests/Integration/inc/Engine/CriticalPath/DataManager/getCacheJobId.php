<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\DataManager::get_cache_job_id
 *
 * @group  CriticalPath
 */
class Test_GetCacheJobId extends TestCase {
	private $transient;

	public function tear_down() {
		parent::tear_down();

		delete_transient( $this->transient );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item_url, $expected, $is_mobile = false ) {
		$this->transient = 'rocket_specific_cpcss_job_' . md5( $item_url ). ( $is_mobile ? '_mobile' : '' );

		// Store the job ID in the transient before running the test.
		if ( false !== $expected ) {
			set_transient( $this->transient, $expected, MINUTE_IN_SECONDS );
		}

		// Run it.
		$data_manager = new DataManager( '', null );
		$actual       = $data_manager->get_cache_job_id( $item_url, $is_mobile );

		$this->assertSame( $expected, $actual );
	}
}
