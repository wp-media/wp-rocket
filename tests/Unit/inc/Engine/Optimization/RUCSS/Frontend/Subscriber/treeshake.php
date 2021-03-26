<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use Brain\Monkey\Functions;
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
class Test_Treeshake extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldOptimizeAsExpected( $config, $mockResponse, $expected ): void {

	}
}
