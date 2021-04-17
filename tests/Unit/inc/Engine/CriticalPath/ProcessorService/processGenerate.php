<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\ProcessorService;

use Mockery;
use WP_Error;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\DataManager;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\ProcessorService::process_generate
 *
 * @group  CriticalPath
 */
class Test_ProcessGenerate extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/ProcessorService/processGenerate.php';

	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDoExpected( $config, $expected ) {
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
		$is_mobile                    = isset( $config['mobile'] )
			? $config['mobile']
			: false;
		$no_fontface                  = isset( $config['fontface'] )
			? $config['fontface']
			: false;
		$item_type                    = isset( $config['type'] )
			? $config['type']
			: 'custom';

		$api_client    = Mockery::mock( APIClient::class );
		$data_manager  = Mockery::mock( DataManager::class );
		$cpcss_service = new ProcessorService( $data_manager, $api_client );

		if ( $request_timeout ) {
			$data_manager->shouldReceive( 'delete_cache_job_id' )->once()->with( $item_url, $is_mobile );
		} else {
			$data_manager->shouldReceive( 'get_cache_job_id' )->once()
				->with( $item_url, $is_mobile )
				->andReturn( $saved_cpcss_job_id );

			if ( false === $saved_cpcss_job_id) {
				// enters send_generation_request()
				if ( $cpcss_post_job_id && 200 === $post_request_response_code ) {
					$api_client->shouldReceive( 'send_generation_request' )
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

					$data_manager->shouldReceive( 'set_cache_job_id' )->once()->with( $item_url, $cpcss_post_job_id, $is_mobile );

					if ( ! in_array( (int) $get_request_response_code, [ 400, 404 ], true ) ) {
						$api_client->shouldReceive( 'get_job_details' )
							->once()
							->with( $cpcss_post_job_id, $item_url, $is_mobile, $item_type )
							->andReturn( $get_request_response_decoded );
						$data_manager->shouldReceive( 'delete_cache_job_id' )->once()->with( $item_url, $is_mobile );

						$data_manager->shouldReceive( 'save_cpcss' )
							->once()
							->with( $item_path, $get_request_response_decoded->data->critical_path, $item_url, $is_mobile, $item_type )
							->andReturn( $save_cpcss );
					} else {
						$api_client->shouldReceive( 'get_job_details' )
							->once()
							->with( $cpcss_post_job_id, $item_url, $is_mobile, $item_type )
							->andReturn( $get_job_details_error );
						$data_manager->shouldReceive( 'delete_cache_job_id' )->once()->with( $item_url, $is_mobile );
					}
				} else {
					$api_client->shouldReceive( 'send_generation_request' )
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

		$generation_params = [
			'is_mobile' => $is_mobile,
			'timeout' => $request_timeout,
			'item_type' => $item_type
		];
		$generated = $cpcss_service->process_generate( $item_url, $item_path, $generation_params );
		if( isset( $expected['success'] ) && ! $expected['success'] ){
			$this->assertSame( $expected['code'], $generated->get_error_code() );
			$this->assertSame( $expected['message'], $generated->get_error_message() );
			$this->assertSame( $expected['data'], $generated->get_error_data() );
		}else{
			$this->assertSame( $expected, $generated );
		}
	}

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}
}
