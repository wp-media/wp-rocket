<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber;
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
	public function testShouldOptimizeAsExpected( $config, $expected ): void {

		$options = Mockery::mock(Options_Data::class );
		$usedCSS = Mockery::mock( UsedCSS::class );
		$apiClient = Mockery::mock( APIClient::class );
		$subscriber = new Subscriber($options, $usedCSS, $apiClient );

		Functions\when( 'rocket_get_constant' )->justReturn( $config['no-optimize'] );
		Functions\when( 'rocket_bypass' )->justReturn( $config['bypass'] );
		Functions\when( 'is_user_logged_in' )->justReturn( $config['logged-in']);

		if ( ! $config['no-optimize'] && ! $config['bypass'] ) {
			$options->shouldReceive( 'get' )
				->once()
				->with( 'remove_unused_css', 0 )
				->andReturn( $config['rucss-enabled'] );
		}

		if ( ! $config['no-optimize'] && ! $config['bypass'] && $config['rucss-enabled'] ) {
			$options->shouldReceive( 'get' )
				->once()
				->with( 'cache_logged_user' , 0 )
				->andReturn( $config['logged-in-cache'] );
		}

		if ( ! $config['no-optimize'] && ! $config['bypass'] && $config['rucss-enabled'] && $config['logged-in-cache'] ) {
			Functions\when( 'home_url' )->justReturn('http://example.com/home');

		}
			$this->assertEquals( $expected, $subscriber->treeshake( $config['html'] ) );
	}
}
