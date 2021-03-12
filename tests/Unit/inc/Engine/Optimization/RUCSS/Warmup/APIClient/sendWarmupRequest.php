<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\APIClient;

use Brain\Monkey;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient::send_warmup_request
 *
 * @group  RUCSS
 */
class Test_APIClient extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( array $atts, int $returnCode, bool $expected ) {

		$apiClient = new APIClient();

		Monkey\Functions\expect( 'wp_parse_args' )
			->once()
			->with( $atts, [
				'url'     => '',
				'type'    => 'css',
				'content' => '',
			] )
			->andReturnFirstArg();

		Monkey\Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://central-saas.wp-rocket.me:30443/warmup',
				[
					'body' => [
						'resources' => [
							$atts,
						]
					]
				]
			)
			->andReturn( $returnCode );

		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )
			->with( $returnCode )
			->andReturnFirstArg();


		$this->assertTrue( $expected === $apiClient->send_warmup_request( $atts ) );
	}
}
