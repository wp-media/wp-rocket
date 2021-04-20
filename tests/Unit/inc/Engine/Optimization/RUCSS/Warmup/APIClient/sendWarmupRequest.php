<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient::send_warmup_request
 *
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::handle_post()
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::check_response()
 * @group  RUCSS
 */
class Test_SendWarmupRequest extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendWarmupRequest( $atts, $success, $mockResponse ): void {
		$response = $this->get_reflective_property( 'response_body', AbstractAPIClient::class );
		$error    = $this->get_reflective_property( 'error_message', AbstractAPIClient::class );

		$args = [
			'body' => [
				'resources' => [
					$atts,
				],
			],
		];

		$options = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
		        ->once()
		        ->with( 'consumer_key', '' )
		        ->andReturn( 'rocket_key' );

		$options->shouldReceive( 'get' )
		        ->once()
		        ->with( 'consumer_email', '' )
		        ->andReturn( 'rocket_email' );

		$apiClient = new APIClient( $options );

		$args['body']['credentials'] = [
			'wpr_email' => 'rocket_email',
			'wpr_key'   => 'rocket_key',
		];

		Functions\when( 'wp_parse_args' )->returnArg( 1 );
		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				$apiClient::API_URL . 'warmup',
				$args
			)
			->andReturn( $mockResponse );

		if ( is_array( $mockResponse ) ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )
				->once()
				->andReturn( $success ? 200 : 400 );

			if ( 200 !== $mockResponse['response']['code'] ) {
				Functions\expect( 'wp_remote_retrieve_response_message' )
					->once()
					->andReturn( $mockResponse['response']['message'] );
			}
		}

		if ( $success ) {
			Functions\expect( 'wp_remote_retrieve_body' )
				->once()
				->andReturn( $mockResponse['body'] );
			$this->assertTrue( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $mockResponse['body'], $response->getValue( $apiClient ) );
		} else {
			$error_message = is_array( $mockResponse )
				? $mockResponse['response']['message']
				: $mockResponse->get_error_message();


			$this->assertFalse( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $error_message, $error->getValue( $apiClient ) );
		}
	}
}
