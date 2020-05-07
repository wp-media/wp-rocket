<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\RESTGenerate;

use Brain\Monkey\Functions;
use WP_REST_Request;
use WP_Rocket\Engine\CriticalPath\RESTGenerate;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTGenerate::generate
 *
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

		// Mocks the various filesystem constants.
		$this->whenRocketGetConstant();
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$post_id                      = isset( $config['post_data'] )
			? $config['post_data']['ID']
			: 0;
		$post_type                    = ! isset( $config['post_data']['post_type'] )
			? 'post'
			: $config['post_data']['post_type'];
		$post_status                  = isset( $config['post_data']['post_status'] )
			? $config['post_data']['post_status']
			: false;
		$post_request_response_code   = ! isset( $config['generate_post_request_data']['code'] )
			? 200
			: $config['generate_post_request_data']['code'];
		$post_request_response_body   = ! isset( $config['generate_post_request_data']['body'] )
			? ''
			: $config['generate_post_request_data']['body'];
		$get_request_response_body    = ! isset( $config['generate_get_request_data']['body'] )
			? ''
			: $config['generate_get_request_data']['body'];
		$saved_cpcss_job_id           = isset( $config['cpcss_job_id'] )
			? $config['cpcss_job_id']
			: false;
		$cpcss_post_job_body          = ! isset( $config['generate_post_request_data']['body'] )
			? ''
			: json_decode( $config['generate_post_request_data']['body'] );
		$cpcss_post_job_id            = ! isset( $cpcss_post_job_body->data->id )
			? false
			: $cpcss_post_job_body->data->id;
		$get_request_response_code    = ! isset( $config['generate_get_request_data']['code'] )
			? 200
			: $config['generate_get_request_data']['code'];
		$get_request_response_decoded = ! isset( $config['generate_get_request_data']['body'] )
			? ''
			: json_decode( $config['generate_get_request_data']['body'] );
		$get_request_response_state   = ! isset( $get_request_response_decoded->data->state )
			? false
			: $get_request_response_decoded->data->state;
		$request_timeout              = isset( $config['request_timeout'] )
			? $config['request_timeout']
			: false;
		$file                         = $this->config['vfs_dir'] . "1/posts/{$post_type}-{$post_id}.css";

		Functions\expect( 'get_post_status' )
			->once()
			->andReturn( $post_status );

		if ( $post_id > 0 && 'publish' === $post_status ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_specific_cpcss_job_' . $post_id )
				->andReturn( $saved_cpcss_job_id );
		}

		if ( $post_id > 0 && 'publish' === $post_status && $cpcss_post_job_id ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_specific_cpcss_job_' . $post_id, $cpcss_post_job_id );
		}

		if ( in_array( (int) $get_request_response_code, [ 400, 404 ], true )
		     || ( 200 === $get_request_response_code && 'complete' === $get_request_response_state )
		     || $request_timeout ) {
			Functions\expect( 'delete_transient' )
				->once()
				->with( 'rocket_specific_cpcss_job_' . $post_id );
		}
		Functions\expect( 'get_post_type' )
			->atMost()
			->times( 1 )
			->andReturn( $post_type );

		Functions\expect( 'get_current_blog_id' )
			->once()
			->andReturn( 1 );
		Functions\expect( 'get_permalink' )
			->atMost()
			->times( 1 )
			->with( $post_id )
			->andReturnUsing(
				function( $post_id ) use ( $expected ) {
					return 'post_not_exists' === $expected['code']
						? false
						: "http://example.org/?p={$post_id}";
				}
			);

		Functions\expect( 'wp_remote_post' )
			->atMost()
			->times( 1 )
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
			->times( 1 )
			->andReturn( $post_request_response_code );

		Functions\expect( 'wp_remote_retrieve_body' )
			->ordered()
			->atMost()
			->times( 1 )
			->with( 'postRequest' )
			->andReturn( $post_request_response_body )
			->andAlsoExpectIt()
			->atMost()
			->times( 1 )
			->with( 'getRequest' )
			->andReturn( $get_request_response_body );

		Functions\expect( 'wp_remote_get' )
			->atMost()
			->times( 1 )
			->andReturn( 'getRequest' );

		Functions\when( 'wp_strip_all_tags' )->returnArg();

		Functions\expect( 'rest_ensure_response' )->once()->andReturnArg( 0 );

		$instance      = new RESTGenerate( $this->filesystem->getUrl( 'wp-content/cache/critical-css/' ) );
		$request       = new WP_REST_Request();
		$request['id'] = $post_id;

		if ( $request_timeout ) {
			$request['timeout'] = $request_timeout;
		}

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
