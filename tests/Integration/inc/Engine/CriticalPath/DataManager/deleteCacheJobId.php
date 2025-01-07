<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\DataManager::delete_cache_job_id
 *
 * @group  CriticalPath
 */
class Test_DeleteCacheJobId extends TestCase {
	private $transient;

	public function tear_down() {
		parent::tear_down();

		delete_transient( $this->transient );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item_url, $expected, $is_mobile ) {
		$this->transient = 'rocket_specific_cpcss_job_' . md5( $item_url ) . ( $is_mobile ? '_mobile' : '' );

		if ( $expected ) {
			set_transient( $this->transient, 1, MINUTE_IN_SECONDS );
		}

		$data_manager = new DataManager( '', null );
		$actual       = $data_manager->delete_cache_job_id( $item_url, $is_mobile );

		$this->assertSame( $expected, $actual );
		$this->assertFalse( get_transient( $this->transient ) );
	}
}
