<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Subscriber;

use Mockery;
use WP_Rocket\Engine\Tracking\Subscriber;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\Subscriber::track_dashboard_banner_dismissed
 * @group  Tracking
 */
class Test_TrackDashboardBannerDismissed extends TestCase {
	public function testShouldSubscribeToTheHook(): void {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame(
			[ 'track_dashboard_banner_dismissed', 10, 1 ],
			$events['rocket_dashboard_banner_dismissed']
		);
	}

	public function testShouldDelegateToTracking(): void {
		$tracking = Mockery::mock( Tracking::class );
		$tracking->shouldReceive( 'track_dashboard_banner_dismissed' )->once()->with( 'imagify' );

		( new Subscriber( $tracking ) )->track_dashboard_banner_dismissed( 'imagify' );
	}
}
