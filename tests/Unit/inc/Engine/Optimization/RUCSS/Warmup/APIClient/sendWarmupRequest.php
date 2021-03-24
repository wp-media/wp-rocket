<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\APIClient;

use Brain\Monkey\Functions as Monkey;
use WP_Error;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient::send_warmup_request
 *
 * @uses \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::handle_post()
 * @uses \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::check_response()

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

		$apiClient = new APIClient();

		Monkey\when( 'wp_parse_args' )->returnArg( 1 );
		Monkey\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://central-saas.wp-rocket.me/warmup',
				$args
			)
			->andReturn( $mockResponse );
		Monkey\expect( 'wp_remote_retrieve_response_code' )
			->once()
			->andReturn( $success ? 200 : 400 );

		if ( $success ) {
			Monkey\expect( 'wp_remote_retrieve_body' )
				->once()
				->andReturn( $mockResponse['body'] );
			$this->assertTrue( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $mockResponse['body'], $response->getValue( $apiClient ) );
		} else {
			$error_message = is_array($mockResponse)
				? $mockResponse['response']['message']
				: $mockResponse->get_error_message();

			Monkey\expect( 'wp_remote_retrieve_response_message' )
				->once()
				->andReturn( $error_message );

			$this->assertFalse( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $error_message, $error->getValue( $apiClient ) );
		}
	}
}
