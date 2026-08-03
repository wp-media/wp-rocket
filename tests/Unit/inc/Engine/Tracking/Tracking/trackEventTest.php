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
 * @covers \WP_Rocket\Engine\Tracking\Tracking::track_event
 * @group  Tracking
 */
class trackEventTest extends TestCase {
	private $optin;
	private $mixpanel;
	private $options;
	private $user;
	private $channel_detector;
	private $tracking;

	protected function set_up(): void {
		parent::set_up();

		$this->optin            = Mockery::mock( Optin::class );
		$this->mixpanel         = Mockery::mock( MixpanelTracking::class );
		$this->options          = Mockery::mock( Options_Data::class );
		$this->user             = Mockery::mock( User::class );
		$this->channel_detector = Mockery::mock( ChannelDetector::class );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( '' );
		$this->mixpanel->shouldReceive( 'identify' )
			->once()
			->with( '' );
		$this->optin->shouldReceive( 'can_track' )
			->once()
			->andReturn( false );

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
			->once()
			->andReturn( $config['can_track'] );

		if ( ! $expected['track_called'] ) {
			$this->mixpanel->shouldNotReceive( 'track' );
			$this->channel_detector->shouldNotReceive( 'detect' );
		} else {
			$this->channel_detector->shouldReceive( 'detect' )
				->once()
				->andReturn( $config['detected_channel'] );

			$this->mixpanel->shouldReceive( 'track' )
				->once()
				->with( $expected['event_name'], $expected['event_data'] );
		}

		$this->tracking->track_event( $config['event_name'], $config['event_data'] );
	}
}
