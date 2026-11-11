<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Controller::track_home_after_analytics_optin
 *
 * @group Admin
 * @group RocketInsights
 */
class Test_TrackHomeAfterAnalyticsOptin extends TestCase {
	private $query;
	private $plan;
	private $tracking;
	private $controller;

	public function set_up() {
		parent::set_up();

		$this->query    = $this->createMock( RocketInsights::class );
		$this->plan     = Mockery::mock( Plan::class );
		$this->tracking = Mockery::mock( Tracking::class );

		$this->controller = new Controller(
			$this->query,
			Mockery::mock( Manager::class ),
			Mockery::mock( Context::class ),
			$this->plan,
			Mockery::mock( GlobalScore::class ),
			Mockery::mock( User::class ),
			Mockery::mock( Options_Data::class ),
			$this->tracking
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHandleTrackingAfterOptIn( $config, $expected ) {
		if ( isset( $config['notice_displayed'] ) ) {
			Functions\when( 'get_option' )
				->justReturn( $config['notice_displayed'] );
		}

		if ( isset( $config['home_url'] ) ) {
			Functions\when( 'home_url' )
				->justReturn( $config['home_url'] );
		}

		if ( array_key_exists( 'row_details', $config ) ) {
			$this->query
				->expects( $this->once() )
				->method( 'get_row' )
				->with( $config['home_url'], true )
				->willReturn( $config['row_details'] );
		} else {
			$this->query
				->expects( $this->never() )
				->method( 'get_row' );
		}

		if ( $expected['should_track'] ) {
			$this->plan
				->shouldReceive( 'get_current_plan' )
				->once()
				->andReturn( $config['current_plan'] );

			$this->tracking
				->shouldReceive( 'track_rocket_insights_test' )
				->once()
				->with( $config['row_details'], [], $config['current_plan'] );
		} else {
			$this->plan->shouldNotReceive( 'get_current_plan' );
			$this->tracking->shouldNotReceive( 'track_rocket_insights_test' );
		}

		$this->controller->track_home_after_analytics_optin( $config['status'] );
	}
}
