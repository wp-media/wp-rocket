<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;


/**
 * Test class covering ::rocket_clean_post_cache_on_status_change
 * @uses   ::rocket_clean_files
 *
 * @group  Common
 * @group  Purge
 */
class TestRocketCleanCachePostOnStatusChange extends TestCase {
	protected $path_to_test_data = 'rocketCleanCachePostOnStatusChange.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBailOutWhenPublicTypeIsFalse( $config, $expected ) {
		$post             = (object) $config['post_data'] ?? [];
		$post_id          = $config['post_data']['ID'] ?? 0;
		$post_type_public = isset( $config['post_type_public'] ) ? (object) $config['post_type_public'] : null; //post_type_public

		Functions\expect('get_post')
			->once()
			->with( $post_id )
			->andReturn( $post );

		Functions\expect('get_post_field')
			->once()
			->with( 'post_status', $post_id )
			->andReturn( 'publish' );

		Functions\expect('get_post_type_object')
			->once()
			->with( $config['post_data']['post_type'] )
			->andReturn( $post_type_public );

		$this->assertNull( rocket_clean_post_cache_on_status_change( $post_id,  $config['post_data'] ) );
	}
}
