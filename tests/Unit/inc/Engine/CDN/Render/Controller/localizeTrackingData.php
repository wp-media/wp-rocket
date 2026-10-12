<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::localize_tracking_data
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::localize_tracking_data
 * @group  CDN
 * @group  RocketCDN
 */
class Test_LocalizeTrackingData extends TestCase {

	public function testShouldLocalizeCdnModeAndStatus(): void {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'get_cdn_state' )->andReturn( 'rocketcdn_free' );
		$context->shouldReceive( 'get_cdn_status' )->andReturn( 'active' );

		$controller = new Controller(
			Mockery::mock( Beacon::class ),
			'',
			$context,
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Options::class ),
			$this->createMock( RocketCDNQuery::class ),
			Mockery::mock( SubscriptionController::class ),
			Mockery::mock( User::class ),
			Mockery::mock( Cache::class )
		);

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'wpr-admin-common',
				'rocket_cdn_mixpanel_data',
				[
					'cdn_mode'   => 'rocketcdn_free',
					'cdn_status' => 'active',
				]
			);

		$controller->localize_tracking_data();
	}
}
