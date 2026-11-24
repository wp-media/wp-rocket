<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\APIHandler\AbstractAPIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\APIHandler\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient::is_feature_disabled
 *
 * @group SaaS
 * @group JobManager
 */
class Test_IsFeatureDisabled extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$options = Mockery::mock( Options_Data::class );

		if ( 'empty_key' === $config['client'] ) {
			$client = new class( $options ) extends AbstractAPIClient {
				public function validate_add_to_queue_response( array $response ): bool {
					return true;
				}
			};
			Functions\expect( 'get_transient' )->never();
		} else {
			$client = new APIClient( $options );
			Functions\expect( 'get_transient' )
				->once()
				->with( 'wp_rocket_rucss_feature_disabled' )
				->andReturn( null === $config['offset'] ? false : time() + $config['offset'] );
		}

		if ( $config['deleted'] ) {
			Functions\expect( 'delete_transient' )->once()->with( 'wp_rocket_rucss_feature_disabled' );
		} else {
			Functions\expect( 'delete_transient' )->never();
		}

		$this->assertSame( $expected, $client->is_feature_disabled() );
	}
}
