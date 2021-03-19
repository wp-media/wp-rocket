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
 * @uses \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::handle_post()
 * @uses \WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient::check_response()
 *
 * @group  RUCSS
 */
class Test_Optimize extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldOptimizeAsExpected( $html, $url, $success, $expected ): void {
		$response = $this->get_reflective_property( 'response_body', AbstractAPIClient::class );
		$error    = $this->get_reflective_property( 'error_message', AbstractAPIClient::class );

		$config = [
			'treeshake'      => 1,
			'wpr_email'      => 'rocketeer@wp-rocket.me',
			'wpr_key'        => 'SuperSecretRocketeerKey',
			'rucss_safelist' => [ 'http://example.com/my/safe/css.css' ],
		];

		$args = [
			'body'    => [
				'html'   => $html,
				'url'    => $url,
				'config' => $config,
			],
			'timeout' => 5,
		];

		$apiClient = new APIClient();

		Monkey\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://central-saas.wp-rocket.me/api',
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
			Monkey\when( 'wp_parse_args' )->returnArg( 1 );
			$this->assertSame(
				$this->format_expected( $expected ),
				$apiClient->optimize( $html, $url, $config )
			);
		} else {
			Monkey\expect( 'wp_remote_retrieve_response_message' )
				->once()
				->andReturn( $expected['message'] );
			$this->assertEquals(
				$expected,
				$apiClient->optimize( $html, $url, $config )
			);
		}
	}

	private function format_expected( $response ) {
		$response_as_array = json_decode( $response, true );

		return [
			'code'            => $response_as_array['code'],
			'message'         => $response_as_array['message'],
			'css'             => $response_as_array['contents']['shakedCSS'],
			'unprocessed_css' => $response_as_array['contents']['unProcessedCss']
		];
	}
}

