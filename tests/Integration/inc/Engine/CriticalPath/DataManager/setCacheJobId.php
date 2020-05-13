<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::set_cache_job_id
 * @group CriticalPath
 * @group  vfs
 */
class Test_SetCacheJobId extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/setCacheJobId.php';

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$job_id   = isset( $config['job_id'] ) ? $config['job_id']     : null;
		$saved    = isset( $config['saved'] ) ? $config['saved']       : false;

		Functions\expect( 'get_current_blog_id' )->once();

		Functions\expect( 'set_transient' )
			->once()
			->with( 'rocket_specific_cpcss_job_'.md5( $item_url ), $job_id, HOUR_IN_SECONDS )
			->andReturn( $saved );

		$data_manager = new DataManager( '' );
		$actual = $data_manager->set_cache_job_id( $item_url, $job_id );

		$this->assertSame($expected['saved'], $actual);

	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
