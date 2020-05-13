<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::delete_cache_job_id
 * @group CriticalPath
 * @group  vfs
 */
class Test_DeleteCacheJobId extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/deleteCacheJobId.php';

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$deleted  = isset( $config['deleted'] )  ? $config['deleted']  : false;

		Functions\expect( 'get_current_blog_id' )->once();

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_specific_cpcss_job_'.md5( $item_url ) )
			->andReturn( $deleted );

		$data_manager = new DataManager( '' );
		$actual       = $data_manager->delete_cache_job_id( $item_url );

		$this->assertSame($expected['deleted'], $actual);

	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
