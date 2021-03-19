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
 * @group  RUCSS
 */
class Test_SendWarmupRequest extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendWarmupRequest( $atts, $success, $expected ): void {
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
			->andReturn( $success
				? [
					'response' => [
						'code' => 200,
						'body' => $expected,
					]
				]
				: new WP_Error( 400, $expected )
			);
		Monkey\expect( 'wp_remote_retrieve_response_code' )
			->once()
			->andReturn( $success ? 200 : 400 );

		if ( $success ) {
			Monkey\expect( 'wp_remote_retrieve_body' )
				->once()
				->andReturn( $expected );
			$this->assertTrue( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $expected, $response->getValue( $apiClient ) );
		} else {
			Monkey\expect( 'wp_remote_retrieve_response_message' )
				->once()
				->andReturn( $expected );
			$this->assertFalse( $apiClient->send_warmup_request( $atts ) );
			$this->assertEquals( $expected, $error->getValue( $apiClient ) );
		}
	}
}
