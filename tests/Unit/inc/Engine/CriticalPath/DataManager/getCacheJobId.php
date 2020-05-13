<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::get_cache_job_id
 * @group CriticalPath
 * @group  vfs
 */
class Test_GetCacheJobId extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/getCacheJobId.php';

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$job_id   = isset( $config['job_id'] ) ? $config['job_id'] : null;

		Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_specific_cpcss_job_'.md5( $item_url ) )
			->andReturn( $job_id );

		$data_manager = new DataManager( '' );
		$actual = $data_manager->get_cache_job_id( $item_url );

		$this->assertSame($expected['job_id'], $actual);

	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
