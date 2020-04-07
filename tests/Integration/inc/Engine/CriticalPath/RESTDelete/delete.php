<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTDelete;

use WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RestTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::delete
 * @uses   ::rocket_get_constant
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Delete extends RestTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTDelete/delete.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$post_id = ! isset( $config['post_data']['post_id'] )
			? $this->factory->post->create( $config['post_data'] )
			: $config['post_data']['post_id'];

		if ( $config['current_user_can'] ) {
			$this->addCriticalPathUserCapabilities();
		}

		$file = $this->config['vfs_dir'] . "1/posts/post-type-{$post_id}.css";

		$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $this->requestDeleteCriticalPath( $post_id ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}
}
