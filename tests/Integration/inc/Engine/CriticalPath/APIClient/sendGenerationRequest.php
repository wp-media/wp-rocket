<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\APIClient;

use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\APIClient::send_generation_request
 * @group CriticalPath
 */
class Test_SendGenerationRequest extends TestCase {
	private $response;

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'bypass_request'] );

		parent::tear_down();
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$is_mobile = isset( $config['is_mobile'] ) ? $config['is_mobile'] : false;

		$this->response = $config['response'];

		add_filter( 'pre_http_request', [ $this, 'bypass_request'] );

		$api_client = new APIClient();
		$actual = $api_client->send_generation_request( $item_url, ['mobile' => (int) $is_mobile] );

		if ( isset( $expected['success'] ) && true === $expected['success'] ) {
			//Assert success.
			$this->assertSame( $expected['success'], $actual->success );
			$this->assertSame( $expected['data'],    (array) $actual->data );
		} else {
			//Assert WP_Error.
			$this->assertInstanceOf(WP_Error::class, $actual);
			$this->assertSame( $expected['code'], $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'], $actual->get_error_data() );
		}
	}

	public function bypass_request() {
		return $this->response;
	}
}
