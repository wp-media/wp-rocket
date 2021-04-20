<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\PHPUnit\Integration\ApiTrait;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient::optimize
 *
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::handle_post()
 * @uses   \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::check_response()
 *
 * @group  RUCSS
 */
class Test_Optimize extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'license.php';

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

		$options = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'consumer_key', '' )
		              ->andReturn( self::getApiCredential( 'ROCKET_KEY' ) );

		$options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'consumer_email', '' )
		              ->andReturn( self::getApiCredential( 'ROCKET_EMAIL' ) );

		$apiClient = new APIClient( $options );

		Functions\when( 'wp_parse_args' )->returnArg( 1 );

		$args['body']['credentials'] = [
			'wpr_email' => self::getApiCredential( 'ROCKET_EMAIL' ),
			'wpr_key'   => self::getApiCredential( 'ROCKET_KEY' ),
		];

		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				$apiClient::API_URL . 'api',
				$args
			)
			->andReturn( $mockResponse );

		if ( is_array( $mockResponse ) ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )
				->once()
				->andReturn( $mockResponse['response']['code'] );

			if ( 200 === $mockResponse['response']['code'] ) {
				Functions\expect( 'wp_remote_retrieve_body' )
					->once()
					->andReturn( $mockResponse['body'] );
			} else {
				Functions\expect( 'wp_remote_retrieve_response_message' )
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
