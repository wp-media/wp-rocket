<?php

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::check_job_status
 *
 * @group  RUCSS
 */
class Test_CheckJobStatus extends TestCase {
	protected $options;
	protected $usedCssQuery;
	protected $api;
	protected $queue;
	protected $usedCss;
	protected $data_manager;
	protected $filesystem;

	protected function setUp(): void {
		parent::setUp();
		$this->options      = Mockery::mock( Options_Data::class );
		$this->usedCssQuery = $this->createMock( UsedCSS_Query::class );
		$this->api          = Mockery::mock( APIClient::class );
		$this->queue        = Mockery::mock( QueueInterface::class );
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->filesystem   = Mockery::mock( Filesystem::class );
		$this->usedCss      = Mockery::mock(
			UsedCSS::class . '[is_allowed,update_last_accessed]',
			[
				$this->options,
				$this->usedCssQuery,
				$this->api,
				$this->queue,
				$this->data_manager,
				$this->filesystem
			]
		);
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Logger::disable_debug();
		$new_job_id = false;
		if ( $config['row_details'] ) {
			$row_details = new UsedCSS_Row( $config['row_details'] );
		} else {
			$row_details = null;
		}
		if ( isset( $config['job_details'] ) ) {
			$job_details = $config['job_details'];
		}
		if ( isset( $config['add_to_queue_response'] ) ) {
			$add_to_queue_response = $config['add_to_queue_response'];
		}
		if ( isset( $job_details['contents']['shakedCSS'] ) ) {
			$css  = $job_details['contents']['shakedCSS'];
			$hash = md5( $css );
		}
		if ( isset( $config['is_used_css_file_written'] ) ) {
			$is_file_written = $config['is_used_css_file_written'];
		}

		$this->usedCssQuery->expects( self::once() )
		                   ->method( 'get_item' )
		                   ->with( $config['job_id'] )
		                   ->willReturn( $row_details );
		if ( ! $row_details ) {
			return;
		}
		//$this->usedCss->expects()->is_home( $row_details->url);
		Functions\expect( 'home_url' )
			->with()
			->zeroOrMoreTimes()
			->andReturn( $row_details->url );

		$this->api->expects()
		          ->get_queue_job_status( $row_details->job_id, $row_details->queue_name, $row_details->is_home )
		          ->andReturn( $job_details );


		if (
			200 !== $job_details['code']
			||
			empty( $job_details['contents'] )
			||
			! isset( $job_details['contents']['shakedCSS'] )
		) {
			if ( $row_details->retries >= 3 ) {
				Actions\expectDone('rocket_preload_unlock_url')->with($config['row_details']['url']);
				$this->usedCssQuery->expects( self::once() )
				                   ->method( 'make_status_failed' )
				                   ->with( $config['job_id'], $job_details['code'], $job_details['message'] );

				$this->usedCss->check_job_status( $config['job_id'] );

				return;
			}

			// on timeout errors with code 408 create new job.
			if ( 408 === $job_details['code'] ) {
				$this->options->expects()->get( 'remove_unused_css_safelist', [] )->andReturn( [] );
				$this->api->expects()->add_to_queue( $row_details->url, [
					'treeshake'      => 1,
					'rucss_safelist' => [],
					'skip_attr' => [],
					'is_mobile'      => $row_details->is_mobile,
					'is_home'        => $row_details->is_home,
				] )
				          ->andReturn( $add_to_queue_response );
				if ( false !== $add_to_queue_response ) {
					$new_job_id = $add_to_queue_response['contents']['jobId'];
					$this->usedCssQuery->expects( self::once() )
					                   ->method( 'update_job_id' )
					                   ->with( $config['job_id'], $new_job_id );
				}
			}
			$this->usedCssQuery->expects( self::once() )
			                   ->method( 'increment_retries' )
			                   ->with( $config['job_id'], $row_details->retries );

			$this->usedCss->check_job_status( $config['job_id'] );

			return;
		}  else {
			Actions\expectDone('rocket_preload_unlock_url')->with($config['row_details']['url']);
		}


		$this->filesystem->shouldReceive( 'write_used_css' )
		                 ->atMost()
		                 ->once()
		                 ->with( $hash, $css )
		                 ->andReturn( $is_file_written );

		if ( ! $is_file_written ) {
			$message = 'RUCSS: Could not write used CSS to the filesystem: ' . $row_details->url;
			$this->usedCssQuery->expects( self::once() )
			                   ->method( 'make_status_failed' )
			                   ->with( $config['job_id'], '', $message );

			$this->usedCss->check_job_status( $config['job_id'] );

			return;
		} else {
			$this->usedCssQuery->expects( self::once() )
			                   ->method( 'make_status_completed' )
			                   ->with( $config['job_id'], $hash );

			Actions\expectDone('rocket_rucss_complete_job_status')->with( $row_details->url, $job_details );
		}
		$this->usedCss->check_job_status( $config['job_id'] );
	}

	protected function configureCreateNewJob( $url, $is_mobile, $add_to_queue_response ) {

		$this->options->expects()->get( 'remove_unused_css_safelist', [] )->andReturn( [] );

		Filters\expectApplied( 'rocket_rucss_safelist' )->with( [] )->andReturn( [] );
		$create_new_job_config = [
			'treeshake'      => 1,
			'rucss_safelist' => [],
			'is_mobile'      => $is_mobile,
			'is_home'        => $url,
		];
		$this->api->expects()
		          ->add_to_queue( $url, $create_new_job_config )
		          ->andReturn( $add_to_queue_response );
	}
}
