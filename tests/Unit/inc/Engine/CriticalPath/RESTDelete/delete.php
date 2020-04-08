<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTDelete;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Engine\CriticalPath\RESTDelete;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTDelete::delete
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Delete extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTDelete/delete.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$post_id   = ! isset( $config['post_data']['post_id'] )
			? $config['post_data']['import_id']
			: $config['post_data']['post_id'];
		$post_type = ! isset( $config['post_data']['post_type'] )
			? 'post'
			: $config['post_data']['post_type'];

		$file = $this->config['vfs_dir'] . "1/posts/{$post_type}-{$post_id}.css";

		if ( 'rest_forbidden' === $expected['code'] ) {
			$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
			return;
		}

		Functions\expect( 'get_post_type' )
			->once()
			->andReturn( $post_type );

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

		Functions\expect( 'rest_ensure_response' )->once()->andReturnFirstArg();

		$instance      = new RESTDelete( 'wp-content/cache/critical-css/' );
		$request       = new WP_REST_Request();
		$request['id'] = $post_id;
		$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $instance->delete( $request ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}
}
