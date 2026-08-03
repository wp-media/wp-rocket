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
 * @covers \WP_Rocket\Engine\Tracking\Tracking::track_rocket_cdn_pause_status
 * @group  Tracking
 * @group  RocketCDN
 */
class TrackRocketCdnPauseStatusTest extends TestCase {
	private $optin;
	private $mixpanel;
	private $options;
	private $user;
	private $channel_detector;
	private $tracking;

	protected function set_up(): void {
		parent::set_up();

		$this->optin    = Mockery::mock( Optin::class );
		$this->mixpanel = Mockery::mock( MixpanelTracking::class );
		$this->options  = Mockery::mock( Options_Data::class );
		$this->user     = Mockery::mock( User::class );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( '' );
		$this->mixpanel->shouldReceive( 'identify' )
			->once()
			->with( '' );
		$this->optin->shouldReceive( 'can_track' )
			->once()
			->andReturn( false );

		$this->channel_detector = Mockery::mock( ChannelDetector::class );
		$this->channel_detector->shouldReceive( 'detect' )->andReturn( ChannelDetector::CHANNEL_UI )->byDefault();

		$this->tracking = new Tracking(
			$this->options,
			$this->optin,
			$this->mixpanel,
			$this->user,
			$this->channel_detector,
			'path/to/templates'
		);

		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( (array) $defaults, (array) $args );
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->optin->shouldReceive( 'can_track' )
			->times( $expected['can_track_count'] )
			->andReturn( $config['can_track'] );

		if ( ! $expected['track_called'] ) {
			$this->mixpanel->shouldNotReceive( 'track' );
		} else {
			$this->mixpanel->shouldReceive( 'track' )
				->once()
				->with(
					'Button Clicked',
					[
						'context'             => 'wp_plugin',
						'interaction_channel' => ChannelDetector::CHANNEL_UI,
						'status'              => $config['status'],
						'trigger'             => $config['trigger'],
						'button'              => 'rocket cdn pause',
					]
				);
		}

		$this->tracking->track_rocket_cdn_pause_status( $config['status'], $config['trigger'] );
	}
}
