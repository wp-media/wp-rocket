<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Renewal::set_dashboard_seen_transient
 *
 * @group License
 */
class Test_SetDashboardSeenTransient extends TestCase {
	private $pricing;
	private $user;
	private $renewal;
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->options =Mockery::mock( Options_Data::class );
		$this->renewal = new Renewal(
				$this->pricing,
				$this->user,
				$this->options,
				'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->user->shouldReceive( 'is_license_expired' )
			->andReturn( $config['expired'] );

		$this->options->shouldReceive( 'get' )
			->with( 'optimize_css_delivery', 0 )
			->andReturn( $config['ocd'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['expire_date'] );

		if ( $expected ) {
			Functions\expect( 'set_transient' )
				->once();
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->renewal->set_dashboard_seen_transient();
	}
}
