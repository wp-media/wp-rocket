<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use Brain\Monkey\Functions as Monkey;
use WP_Error;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient::optimize
 *
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::handle_post()
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::check_response()
 *
 * @group  RUCSS
 */
class Test_Optimize extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldOptimizeAsExpected( $config, $mockResponse, $expected ): void {
		$response = $this->get_reflective_property( 'response_body', AbstractAPIClient::class );
		$error    = $this->get_reflective_property( 'error_message', AbstractAPIClient::class );

		$args = [
			'body'    => [
				'html'   => $config['html'],
				'url'    => $config['url'],
				'config' => $config['options'],
			],
			'timeout' => 5,
		];

		$apiClient = new APIClient();

		Monkey\when( 'wp_parse_args' )->returnArg( 1 );

		Monkey\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://central-saas.wp-rocket.me/api',
				$args
			)
			->andReturn( $mockResponse );

		if ( is_array( $mockResponse ) ) {
			Monkey\expect( 'wp_remote_retrieve_response_code' )
				->once()
				->andReturn( $mockResponse['response']['code'] );

			if ( 200 === $mockResponse['response']['code'] ) {
				Monkey\expect( 'wp_remote_retrieve_body' )
					->once()
					->andReturn( $mockResponse['body'] );
			} else {
				Monkey\expect( 'wp_remote_retrieve_response_message' )
					->once()
					->andReturn( $mockResponse['response']['message'] );
			}
		}

		$this->assertSame(
			$expected,
			$apiClient->optimize( $config['html'], $config['url'], $config['options'] )
		);
	}
}
