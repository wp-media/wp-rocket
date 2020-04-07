<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTDelete;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Engine\CriticalPath\RESTDelete;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::delete
 * @uses   ::rocket_get_constant
 * @group  CriticalPath
 * @group  vfs
 * @group  thisone
 */
class Test_Delete extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTDelete/delete.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$post_id = ! isset( $config['post_data']['post_id'] )
			? $config['post_data']['import_id']
			: $config['post_data']['post_id'];

		$file = $this->config['vfs_dir'] . "1/posts/post-type-{$post_id}.css";

		if ( 'rest_forbidden' === $expected['code'] ) {
			$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
			return;
		}

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )
			->andReturn( $this->config['vfs_dir'] );
		Functions\expect( 'get_current_blog_id' )
			->once()
			->andReturn( 1 );
		Functions\expect( 'get_permalink' )
			->once()
			->with( $post_id )
			->andReturnUsing( function ( $post_id ) use ( $expected ) {
				return 'post_not_exists' === $expected['code']
					? false
					: "http://example.org/?p={$post_id}";
			} );

		Functions\expect( 'rest_ensure_response' )->once()->andReturn( $expected );

		$instance      = new RESTDelete();
		$request       = new WP_REST_Request();
		$request['id'] = $post_id;

		$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $instance->delete( $request ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}
}
