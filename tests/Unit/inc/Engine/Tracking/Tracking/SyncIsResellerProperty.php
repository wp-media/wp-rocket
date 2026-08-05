<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\Tracking::sync_is_reseller_property
 * @group  Tracking
 */
class Test_SyncIsResellerProperty extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config ): void {
		$consumer_email = 'consumer@example.org';
		$hashed_email   = 'hashed-consumer-email';

		$optin    = Mockery::mock( Optin::class );
		$mixpanel = Mockery::mock( MixpanelTracking::class );
		$options  = Mockery::mock( Options_Data::class );
		$user     = Mockery::mock( User::class );

		$options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( $consumer_email );

		$mixpanel->shouldReceive( 'identify' )
			->once()
			->with( $consumer_email );

		$optin->shouldReceive( 'can_track' )
			->once()
			->andReturn( $config['can_track'] );

		if ( ! $config['can_track'] ) {
			$user->shouldNotReceive( 'is_reseller_account' );
			$mixpanel->shouldNotReceive( 'hash' );
			$mixpanel->shouldNotReceive( 'set_user_property' );
		} elseif ( $config['transient_exists'] ) {
			Functions\when( 'get_transient' )->justReturn( 1 );
			$user->shouldNotReceive( 'is_reseller_account' );
			$mixpanel->shouldNotReceive( 'hash' );
			$mixpanel->shouldNotReceive( 'set_user_property' );
		} else {
			Functions\when( 'get_transient' )->justReturn( false );
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_mixpanel_reseller_synced', 1, DAY_IN_SECONDS );

			$user->shouldReceive( 'is_reseller_account' )
				->once()
				->andReturn( $config['is_reseller'] );

			$mixpanel->shouldReceive( 'hash' )
				->once()
				->with( $consumer_email )
				->andReturn( $hashed_email );

			$mixpanel->shouldReceive( 'set_user_property' )
				->once()
				->with( $hashed_email, 'is_reseller', $config['is_reseller'] );
		}

		new Tracking( $options, $optin, $mixpanel, $user, 'path/to/templates' );
	}
}
