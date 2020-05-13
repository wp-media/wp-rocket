<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\APIClient;

use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\APIClient::get_job_details
 * @group CriticalPath
 * @group  vfs
 */
class Test_GetJobDetails extends FilesystemTestCase {

	protected $path_to_test_data = '/inc/Engine/CriticalPath/APIClient/getJobDetails.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$item_url      = isset( $config['item_url'] ) ? $config['item_url'] : '';
		$job_id        = isset( $config['job_id'] ) ? $config['job_id'] : 0;
		$response_code = ! isset( $config['response_data']['code'] ) ? 200 : $config['response_data']['code'];
		$response_body = ! isset( $config['response_data']['body'] ) ? '' : $config['response_data']['body'];

		Functions\expect( 'wp_remote_get' )
			->atMost()
			->times( 1 )
			->with(
				'https://cpcss.wp-rocket.me/api/job/'.$job_id."/"
			)
			->andReturn( 'getRequest' );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->atMost()
			->times( 1 )
			->with( 'getRequest' )
			->andReturn( $response_code );

		Functions\expect( 'wp_remote_retrieve_body' )
			->atMost()
			->times( 1 )
			->with( 'getRequest' )
			->andReturn( $response_body );

		$api_client = new APIClient();
		$actual = $api_client->get_job_details( $job_id, $item_url );

		if( isset( $expected['status'] ) && 200 === $expected['status'] ){
			//Assert success.
			$this->assertSame( $expected['status'], $actual->status );
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
