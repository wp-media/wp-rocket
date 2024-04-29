<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\ProcessorService;

use Mockery;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\ProcessorService::process_generate
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_ProcessGenerate extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/ProcessorService/processGenerate.php';

	private static $container;
	private static $user_id;
	private $api_client;
	private $processor;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id   = $factory->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function set_up() {
		parent::set_up();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->processor  = new ProcessorService( self::$container->get( 'cpcss_data_manager' ), $this->api_client );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {

		$post_id                       = isset( $config['post_data'] )
			? $config['post_data']['ID']
			: 0;
		$post_type                     = ! isset( $config['post_data']['post_type'] )
			? 'post'
			: $config['post_data']['post_type'];
		$post_status                   = isset( $config['post_data']['post_status'] )
			? $config['post_data']['post_status']
			: false;
		$post_request_response_code    = ! isset( $config['generate_post_request_data']['code'] )
			? 200
			: $config['generate_post_request_data']['code'];
		$saved_cpcss_job_id            = isset( $config['cpcss_job_id'] )
			? $config['cpcss_job_id']
			: false;
		$cpcss_post_job_body           = ! isset( $config['generate_post_request_data']['body'] )
			? ''
			: json_decode( $config['generate_post_request_data']['body'] );
		$cpcss_post_job_id             = ! isset( $cpcss_post_job_body->data->id )
			? false
			: $cpcss_post_job_body->data->id;
		$get_request_response_code     = ! isset( $config['generate_get_request_data']['code'] )
			? 200
			: $config['generate_get_request_data']['code'];
		$get_request_response_decoded  = ! isset( $config['generate_get_request_data']['body'] )
			? ''
			: json_decode( $config['generate_get_request_data']['body'] );
		$request_timeout               = isset( $config['request_timeout'] )
			? $config['request_timeout']
			: false;
		$item_path = isset( $config['item_path'] ) ? $config['item_path'] : '';
		$item_url  = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$save_cpcss                    =  ! isset( $config['save_cpcss'] )
			? true
			: $config['save_cpcss'];
		$send_generation_request_error = ! isset( $config['send_generation_request_error'] )
			? ''
			: $config['send_generation_request_error'];
		$get_job_details_error         = ! isset( $config['get_job_details_error'] )
			? ''
			: $config['get_job_details_error'];
		$file                         = $this->config['vfs_dir'] . "1/".$item_path;
		$is_mobile                    = isset( $config['mobile'] )
			? $config['mobile']
			: false;
		$no_fontface                    = isset( $config['no_fontface'] )
			? $config['no_fontface']
			: false;
		$item_type                    = isset( $config['type'] )
			? $config['type']
			: 'custom';

		if ( ! $request_timeout ) {
			if ( false === $saved_cpcss_job_id) {
				// enters send_generation_request()
				if ( $cpcss_post_job_id && 200 === $post_request_response_code ) {
					$this->api_client->shouldReceive( 'send_generation_request' )
						->once()
						->with(
							$item_url,
							[
								'mobile'     => $is_mobile,
								'nofontface' => $no_fontface,
							],
							$item_type
						)
						->andReturn( $cpcss_post_job_body );

					if ( ! in_array( (int) $get_request_response_code, [ 400, 404 ], true ) ) {
						$this->api_client->shouldReceive( 'get_job_details' )
							->once()
							->with( $cpcss_post_job_id, $item_url, $is_mobile, $item_type )
							->andReturn( $get_request_response_decoded );
					} else {
						$this->api_client->shouldReceive( 'get_job_details' )
							->once()
							->with( $cpcss_post_job_id, $item_url, $is_mobile, $item_type )
							->andReturn( $get_job_details_error );
					}
				} else {
					$this->api_client->shouldReceive( 'send_generation_request' )
						->once()
						->with(
							$item_url,
							[
								'mobile'     => $is_mobile,
								'nofontface' => $no_fontface,
							],
							$item_type
						)
						->andReturn( $send_generation_request_error );
				}
			}
		}

		if ( isset( $save_cpcss ) && is_wp_error( $save_cpcss ) ) {
			$this->filesystem->chmod( 'wp-content/cache/critical-css/1/', 0444 );
		}

		$additional_params = [
			'timeout'   => $request_timeout,
			'is_mobile' => $is_mobile,
			'item_type' => $item_type
		];
		$generated = $this->processor->process_generate( $item_url, $item_path, $additional_params );

		if ( isset( $expected['success'] ) && ! $expected['success'] ) {
			$this->assertSame( $expected['code'], $generated->get_error_code() );
			$this->assertSame( $expected['message'], $generated->get_error_message() );
			$this->assertSame( $expected['data'], $generated->get_error_data() );
		} else {
			$this->assertSame( $expected, $generated );
		}

		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}
}
