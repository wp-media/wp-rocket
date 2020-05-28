<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::set_cache_job_id
 *
 * @group  CriticalPath
 */
class Test_SetCacheJobId extends TestCase {
	private $transient;

	public function tearDown() {
		parent::tearDown();

		delete_transient( $this->transient );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item_url, $job_id ) {
		$this->transient = 'rocket_specific_cpcss_job_' . md5( $item_url );

		$data_manager = new DataManager( '', null );
		$actual       = $data_manager->set_cache_job_id( $item_url, $job_id );

		$this->assertTrue( $actual );
		$this->assertSame( $job_id, get_transient( $this->transient ) );
	}
}
