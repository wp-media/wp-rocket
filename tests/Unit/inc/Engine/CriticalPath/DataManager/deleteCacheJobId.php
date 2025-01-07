<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\DataManager::delete_cache_job_id
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_DeleteCacheJobId extends TestCase {

	protected function setUp() : void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item_url, $expected, $is_mobile ) {
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_specific_cpcss_job_' . md5( $item_url ) . ( $is_mobile ? '_mobile' : '' ) )
			->andReturn( $expected );

		$data_manager = new DataManager( '', null );
		$actual       = $data_manager->delete_cache_job_id( $item_url, $is_mobile );

		$this->assertSame( $expected, $actual );
	}
}
