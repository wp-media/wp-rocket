<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\APIClient;

use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\APIClient::send_generation_request
 * @group CriticalPath
 * @group  vfs
 */
class Test_SendGenerationRequest extends FilesystemTestCase {

	protected $path_to_test_data = '/inc/Engine/CriticalPath/APIClient/sendGenerationRequest.php';

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url      = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$response_code = ! isset( $config['response_data']['code'] ) ? 200 : $config['response_data']['code'];
		$response_body = ! isset( $config['response_data']['body'] ) ? '' : $config['response_data']['body'];

		Functions\expect( 'wp_remote_post' )
			->atMost()
			->times( 1 )
			->with(
				'https://cpcss.wp-rocket.me/api/job/',
				[
					'body' => [
						'url' => $item_url,
					],
				]
			)
			->andReturn( 'postRequest' );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->atMost()
			->times( 1 )
			->with( 'postRequest' )
			->andReturn( $response_code );

		Functions\expect( 'wp_remote_retrieve_body' )
			->atMost()
			->times( 1 )
			->with( 'postRequest' )
			->andReturn( $response_body );

		$api_client = new APIClient();
		$actual = $api_client->send_generation_request( $item_url );

		if( isset( $expected['success'] ) && true === $expected['success'] ){
			//Assert success.
			$this->assertSame( $expected['success'], $actual->success );
			$this->assertSame( $expected['data'],    (array) $actual->data );
		}else{
			//Assert WP_Error.
			$this->assertInstanceOf(WP_Error::class, $actual);
			$this->assertSame( $expected['code'], $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'], $actual->get_error_data() );
		}
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
