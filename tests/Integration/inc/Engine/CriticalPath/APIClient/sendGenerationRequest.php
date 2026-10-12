<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\APIClient;

use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Error;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\APIClient::send_generation_request
 * @group CriticalPath
 */
class Test_SendGenerationRequest extends TestCase {
	use HttpRequestTrait;

	public function set_up() {
		parent::set_up();

		$this->setup_http();
	}

	public function tear_down() {
		$this->tear_down_http();

		parent::tear_down();
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url  = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$is_mobile = isset( $config['is_mobile'] ) ? $config['is_mobile'] : false;

		$this->config['http'] = [ APIClient::API_URL => $config['response'] ];

		$api_client = new APIClient();

		$actual = (object) $api_client->send_generation_request( $item_url, [ 'mobile' => (int) $is_mobile ] );

		if ( isset( $expected['success'] ) && true === $expected['success'] ) {
			// Assert success.
			$this->assertSame( $expected['success'], $actual->success );
			$this->assertSame( $expected['data'],    (array) $actual->data );
		} else {
			// Assert WP_Error.
			$this->assertInstanceOf( WP_Error::class, $actual );
			$this->assertSame( $expected['code'], $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'], $actual->get_error_data() );
		}
	}
}
