<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use WP_Site;
use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTWPPost::delete
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Delete extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/delete.php';

	protected function doTest( $site_id, $config, $expected ) {
		$post_id   = ! isset( $config['post_data']['post_id'] )
			? $this->factory->post->create( $config['post_data'] )
			: $config['post_data']['post_id'];
		$post_type = ! isset( $config['post_data']['post_type'] )
			? 'post'
			: $config['post_data']['post_type'];

		$file          = $this->config['vfs_dir'] . "{$site_id}/posts/{$post_type}-{$post_id}.css";
		$cspcss_before = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] . "{$site_id}/" ) );

		$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $this->doRestDelete( "/wp-rocket/v1/cpcss/post/{$post_id}" ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );

		// Check that only the specific file is deleted, but all others remain.
		if ( $config['cpcss_exists_before'] && false === $config['cpcss_exists_after'] ) {
			$cspcss_after = array_diff( $cspcss_before, $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] . "{$site_id}/" ) ) );
			$this->assertSame( [ $this->filesystem->getUrl( $file ) ], array_values( $cspcss_after ) );
		}
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDoExpectedWhenNotMultisite( $config, $expected ) {
		if ( $config['current_user_can'] ) {
			$this->setUpUser();
		}

		$this->doTest( 1, $config, $expected );
	}

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	protected function setUpUser() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );

		$user_id = $this->factory->user->create(
			[ 'role' => 'administrator' ]
		);

		wp_set_current_user( $user_id );
	}
}
