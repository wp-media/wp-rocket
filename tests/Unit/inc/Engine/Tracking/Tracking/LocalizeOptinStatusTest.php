<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group Tracking
 */
class LocalizeOptinStatusTest extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->rocket_version = '3.19';

		$optin    = Mockery::mock( Optin::class );
		$mixpanel = Mockery::mock( MixpanelTracking::class );
		$options  = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( $config['consumer_email'] );

		// Mock the identify method that is called in the constructor
		$mixpanel->shouldReceive( 'identify' )
			->with( $config['consumer_email'] )
			->andReturnNull();

		// Mock the hash method when consumer_email is not empty
		if ( ! empty( $config['consumer_email'] ) ) {
			$mixpanel->shouldReceive( 'hash' )
				->with( $config['consumer_email'] )
				->andReturn( hash( 'sha224', $config['consumer_email'] ) );
		}

		$tracking = new Tracking( $options, $optin, $mixpanel, '' );

		Functions\when( 'current_user_can' )->justReturn( $config['user_can_manage'] );

		if ( $config['user_can_manage'] ) {
			$optin->shouldReceive( 'is_enabled' )
				->once()
				->andReturn( $config['optin_enabled'] );
		}

		if ( null === $config['request_uri'] ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $config['request_uri'];
		}

		if ( $expected['should_localize'] ) {
			Functions\expect( 'wp_localize_script' )
				->once()
				->with( 'wpr-admin-common', 'rocket_mixpanel_data', $expected['data'] );

			Functions\when( 'esc_url_raw' )->returnArg();
			Functions\when( 'wp_unslash' )->returnArg();
		} else {
			Functions\expect( 'wp_localize_script' )->never();
		}

		$tracking->localize_optin_status();
	}
}
