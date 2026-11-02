<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Mockery;
use ReflectionMethod;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Renewal::get_license_pricing_data
 *
 * @group License
 */
class GetLicensePricingData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$pricing = Mockery::mock( Pricing::class );
		$user    = Mockery::mock( User::class );
		$renewal = new Renewal( $pricing, $user, Mockery::mock( Options_Data::class ), 'views' );

		$user->shouldReceive( 'get_license_type_slug' )->andReturn( $config['slug'] );
		$user->shouldReceive( 'get_license_type' )->andReturn( $config['license_type'] );

		$pricing->shouldReceive( 'get_pricing_by_type' )->with( $config['slug'] )->andReturn( $config['type_pricing'] );
		$pricing->shouldReceive( 'get_plus_websites_count' )->andReturn( 3 );
		$pricing->shouldReceive( 'get_single_websites_count' )->andReturn( 1 );
		$pricing->shouldReceive( 'get_single_pricing' )->andReturn( (object) [ 'name' => 'single' ] );
		$pricing->shouldReceive( 'get_plus_pricing' )->andReturn( (object) [ 'name' => 'plus' ] );
		$pricing->shouldReceive( 'get_infinite_pricing' )->andReturn( (object) [ 'name' => 'infinite' ] );

		$method = new ReflectionMethod( Renewal::class, 'get_license_pricing_data' );
		$method->setAccessible( true );

		$this->assertEquals( $expected, $method->invoke( $renewal ) );
	}
}
