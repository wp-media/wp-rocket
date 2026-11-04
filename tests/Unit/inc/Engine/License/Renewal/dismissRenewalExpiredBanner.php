<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Renewal::dismiss_renewal_expired_banner
 *
 * @group License
 */
class DismissRenewalExpiredBanner extends TestCase {
	private $pricing;
	private $user;
	private $renewal;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->renewal =  new Renewal(
			$this->pricing,
			$this->user,
			Mockery::mock( Options_Data::class ),
			'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		Functions\when( 'current_user_can' )->justReturn( $config['cap'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_renewal_banner_1' )
			->andReturn( $config['transient'] );

		if ( $expected ) {
			$this->user->shouldReceive( 'is_trial_customer' )
				->once()
				->andReturn( $config['trial'] ?? false );
			Actions\expectDone( 'rocket_mixpanel_track_event' )
				->once()
				->with( 'Dashboard Banner Dismissed', [ 'banner' => ( $config['trial'] ?? false ) ? 'trial_expired' : 'license_expired' ] );

			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_renewal_banner_1', 1, MONTH_IN_SECONDS );

			Functions\expect( 'wp_send_json_success' )->once();
		} else {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->never();
			Functions\expect( 'set_transient' )->never();
			Functions\expect( 'wp_send_json_success' )->never();
		}

		$this->renewal->dismiss_renewal_expired_banner();
	}
}
