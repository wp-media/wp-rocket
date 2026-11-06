<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\APIClient;

use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Error;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\APIClient::get_job_details
 *
 * @group  CriticalPath
 */
class Test_GetJobDetails extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->setUpMocks( $config );

		$is_mobile = ( isset( $config['is_mobile'] ) ) ? $config['is_mobile'] : false;

		$api_client = new APIClient();
		$actual     = $api_client->get_job_details( $config['job_id'], $config['item_url'], $is_mobile );

		if ( isset( $expected['status'] ) && 200 === $expected['status'] ) {
			// Assert success.
			$this->assertSame( $expected['status'], $actual->status );
			$this->assertSame( $expected['data'], (array) $actual->data );

		} else {
			// Assert WP_Error.
			$this->assertInstanceOf( WP_Error::class, $actual );
			$this->assertSame( $expected['code'], $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'], $actual->get_error_data() );
		}
	}

	private function setUpMocks( $config ) {
		Functions\expect( 'wp_remote_get' )
			->once()
			->with( "https://cpcss.wp-rocket.me/api/job/{$config['job_id']}/" )
			->andReturn( 'getRequest' );

		$response = json_decode( $config['response_data']['body'] );
		if ( ! is_object( $response ) || ! property_exists( $response, 'status' ) ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )
				->once()
				->with( 'getRequest' )
				->andReturn( $config['response_data']['code'] );
		}

		Functions\expect( 'wp_remote_retrieve_body' )
			->once()
			->with( 'getRequest' )
			->andReturn( $config['response_data']['body'] );
	}
}
