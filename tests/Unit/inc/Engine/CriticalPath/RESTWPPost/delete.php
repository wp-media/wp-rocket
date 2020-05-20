<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTWPPost;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Engine\CriticalPath\RESTWPPost;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTWPPost::delete
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Delete extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/delete.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}

	/**
	 * @dataProvider dataProvider
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

		if( 'post_not_exists' !== $expected['code'] ){
			Functions\expect( 'get_post_type' )
				->once()
				->andReturn( $post_type );
		}else{
			Functions\expect( 'get_post_type' )
				->never();
		}

		Functions\expect( 'get_current_blog_id' )
			->once()
			->andReturn( 1 );
		Functions\expect( 'get_permalink' )
			->with( $post_id )
			->andReturnUsing( function ( $post_id ) use ( $expected ) {
				return 'post_not_exists' === $expected['code']
					? false
					: "http://example.org/?p={$post_id}";
			} );

		//is_wp_error is called two times at normal/ideal case.
		//validate_item_for_delete
		//delete_cpcss
		Functions\expect( 'is_wp_error' )
			->andReturn(
				('post_not_exists' === $expected['code']),
				in_array($expected['code'], ['cpcss_not_exists', 'cpcss_deleted_failed'])
			);

		Functions\expect( 'rest_ensure_response' )->once()->andReturnFirstArg();

		$api_client    = new APIClient();
		$data_manager  = new DataManager('wp-content/cache/critical-css/', $this->filesystem);
		$cpcss_service = new ProcessorService( $data_manager, $api_client );
		$instance      = new RESTWPPost( $cpcss_service );
		$request       = new WP_REST_Request();
		$request['id'] = $post_id;

		$this->assertSame( $config['cpcss_exists_before'], $this->filesystem->exists( $file ) );
		$this->assertSame( $expected, $instance->delete( $request ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}
}
