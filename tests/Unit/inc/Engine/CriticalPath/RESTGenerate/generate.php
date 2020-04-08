<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTGenerate;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Engine\CriticalPath\RESTGenerate;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTGenerate::generate
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Generate extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTGenerate/generate.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
	}

	public function setUp() {
		parent::setUp();

		$this->filesystem->chmod(  'wp-content/cache/critical-css/index.php', 0644 );
		$this->filesystem->chmod(  'wp-content/cache/critical-css/', 0755 );
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
		$post_status = ! isset( $config['post_data']['post_status'] )
			? 'publish'
			: $config['post_data']['post_status'];
		$generate_post_request_response_code = ! isset( $config['generate_post_request_data']['code'] )
			? 200
			: $config['generate_post_request_data']['code'];
		$generate_post_request_response_body = ! isset( $config['generate_post_request_data']['body'] )
			? ''
			: $config['generate_post_request_data']['body'];
		$generate_get_request_response_code = ! isset( $config['generate_get_request_data']['code'] )
			? 200
			: $config['generate_get_request_data']['code'];
		$generate_get_request_response_body = ! isset( $config['generate_get_request_data']['body'] )
			? ''
			: $config['generate_get_request_data']['body'];

		$file = $this->config['vfs_dir'] . "1/posts/{$post_type}-{$post_id}.css";

		Functions\expect( 'get_post_status' )
			->once()
			->andReturn( $post_status );

		Functions\expect( 'get_post_type' )
			->atMost()
			->times(1)
			->andReturn( $post_type );

		Functions\expect( 'get_current_blog_id' )
			->once()
			->andReturn( 1 );
		Functions\expect( 'get_permalink' )
			->atMost()
			->times(1)
			->with( $post_id )
			->andReturnUsing( function ( $post_id ) use ( $expected ) {
				return 'post_not_exists' === $expected['code']
					? false
					: "http://example.org/?p={$post_id}";
			} );

		Functions\expect( 'wp_remote_post' )
			->atMost()
			->times(1)
			->with(
				'https://cpcss.wp-rocket.me/api/job/',
				[
					'body' => [
						'url' => "http://example.org/?p={$post_id}",
					],
				]
			)
			->andReturn( 'postRequest' );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->atMost()
			->times(1)
			->andReturn( $generate_post_request_response_code );

		Functions\expect( 'wp_remote_retrieve_body' )
			->ordered()
			->atMost()
			->times(1)
			->with( 'postRequest' )
			->andReturn( $generate_post_request_response_body )
			->andAlsoExpectIt()
			->atMost()
			->times(1)
			->with( 'getRequest' )
			->andReturn( $generate_get_request_response_body );

		Functions\expect( 'wp_remote_get' )
			->atMost()
			->times(1)
			->andReturn( $generate_get_request_response_code )
			->andReturn( 'getRequest');

		Functions\when( 'wp_strip_all_tags' )->returnArg();

		Functions\expect( 'rest_ensure_response' )->once()->andReturnArg(0);

		$instance      = new RESTGenerate( $this->filesystem->getUrl( 'wp-content/cache/critical-css/' ) );
		$request       = new WP_REST_Request();
		$request['id'] = $post_id;

		$this->assertSame( $expected, $instance->generate( $request ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}
}
