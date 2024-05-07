<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\APIClient;

use Brain\Monkey\Functions;
use WP_Error;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\APIClient::send_generation_request
 * @group CriticalPath
 */
class Test_SendGenerationRequest extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$is_mobile = isset( $config['is_mobile'] ) ? $config['is_mobile'] : false;
		$block_external = isset( $config['block_external'] ) ? $config['block_external'] : false;

		$post_request_response = 'postRequest';
		if ( $block_external ) {
			$post_request_response = new WP_Error('code', 'User has blocked requests through HTTP.');
		}

		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				'https://cpcss.wp-rocket.me/api/job/',
				[
					'body' => [
						'url' => $item_url,
						'mobile' => (int) $is_mobile
					],
				]
			)
			->andReturn( $post_request_response );

		if( ! $block_external ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )
				->once()
				->with( 'postRequest' )
				->andReturn( $config['response']['response']['code'] );

			Functions\expect( 'wp_remote_retrieve_body' )
				->once()
				->with( 'postRequest' )
				->andReturn( $config['response']['body'] );
		}

		$api_client = new APIClient();
		$actual = $api_client->send_generation_request( $item_url, ['mobile' => (int) $is_mobile] );

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

}
