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
 * @group Tracking
 */
class MigrateOptinTest extends TestCase {
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
		Functions\when( 'is_admin' )->justReturn( true );
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
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'analytics_enabled', false )
			->andReturn( $config['analytics_enabled'] );

		if ( ! $expected ) {
			$this->optin->shouldNotReceive( 'enable' );
		} else {
			$this->optin->shouldReceive( 'enable' )
				->once();
		}

		$this->tracking->migrate_optin( $config['new_version'], $config['old_version'] );
	}
}
