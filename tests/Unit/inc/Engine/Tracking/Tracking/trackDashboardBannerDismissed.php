<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Tracking\ChannelDetector;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\Tracking::track_dashboard_banner_dismissed
 * @group  Tracking
 */
class Test_TrackDashboardBannerDismissed extends TestCase {
	private $optin;
	private $mixpanel;
	private $tracking;

	protected function set_up(): void {
		parent::set_up();

		$this->optin    = Mockery::mock( Optin::class );
		$this->mixpanel = Mockery::mock( MixpanelTracking::class );
		$options        = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( '' );
		$this->mixpanel->shouldReceive( 'identify' )->once()->with( '' );
		$this->optin->shouldReceive( 'can_track' )->once()->andReturn( false );
		Functions\when( 'is_admin' )->justReturn( true );

		$channel_detector = Mockery::mock( ChannelDetector::class );
		$channel_detector->shouldReceive( 'detect' )->andReturn( ChannelDetector::CHANNEL_UI );

		$this->tracking = new Tracking(
			$options,
			$this->optin,
			$this->mixpanel,
			Mockery::mock( User::class ),
			$channel_detector,
			'path/to/templates'
		);

		Functions\when( 'esc_url_raw' )->returnArg();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Functions\when( 'wp_get_referer' )->justReturn( $config['referer'] );
		$this->optin->shouldReceive( 'can_track' )->andReturn( $config['can_track'] );

		if ( ! $expected['track_called'] ) {
			$this->mixpanel->shouldNotReceive( 'track' );
		} else {
			// Only 'rocket_manage_options' is granted: the explicit capability must be forwarded, as the default one would drop the event.
			Functions\when( 'current_user_can' )->alias(
				function ( $cap ) {
					return 'rocket_manage_options' === $cap;
				}
			);

			$this->mixpanel->shouldReceive( 'track' )
				->once()
				->with(
					'Dashboard Banner Dismissed',
					[
						'context'             => 'wp_plugin',
						'banner'              => $config['banner'],
						'interaction_channel' => ChannelDetector::CHANNEL_UI,
						'path'                => $expected['path'],
					],
					'rocket_manage_options'
				);
		}

		$this->tracking->track_dashboard_banner_dismissed( $config['banner'] );
	}
}
