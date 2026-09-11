<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\Rest;

require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Response.php';

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller as RenderController;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\Rest;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_cdn_mode
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_cdn_mode
 * @group  CDN
 * @group  RocketCDN
 */
class Test_SaveCdnMode extends TestCase {

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $query;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * @var Mockery\MockInterface|RenderController
	 */
	private $render_controller;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	public function set_up(): void {
		parent::set_up();

		$this->stubTranslationFunctions();

		$this->query                   = $this->createMock( RocketCDNQuery::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->render_controller       = Mockery::mock( RenderController::class );
		$this->context                 = Mockery::mock( Context::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
	}

	private function get_rest(): Rest {
		return new Rest(
			$this->query,
			$this->options,
			$this->options_api,
			$this->render_controller,
			$this->context,
			$this->subscription_controller
		);
	}

	private function setup_apply_cdn_mode_mocks(): void {
		$this->options->shouldReceive( 'set' )->with( 'cdn', Mockery::any() );
		$this->options->shouldReceive( 'set' )->with( 'cdn_type', Mockery::any() );
		$this->options->shouldReceive( 'set' )->with( 'cdn_state', Mockery::any() );
		$this->options->shouldReceive( 'get_options' )->andReturn( [] );
		$this->options_api->shouldReceive( 'set' )->with( 'settings', [] );
	}

	private function setup_get_pages_data_mocks( int $count = 0, bool $is_loading = false ): void {
		$this->query->method( 'get_all' )->willReturn( [] );
		$this->query->method( 'get_total_count' )->willReturn( $count );
		$this->render_controller->shouldReceive( 'get_built_in_page_list' )->andReturn( '' );
		$this->render_controller->shouldReceive( 'get_status_indicator_html' )->with( $count )->andReturn( '' );
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )->andReturn( $is_loading );
		$this->context->shouldReceive( 'get_free_page_limit' )->andReturn( 3 );
	}

	/**
	 * When mode is free and no active subscription, schedule_subscription_creation must be called.
	 */
	public function testShouldScheduleCreationWhenFreeWithNoActiveSubscription(): void {
		$this->render_controller->shouldReceive( 'should_reject_rocketcdn_activation' )->andReturn( false );
		$this->subscription_controller->shouldReceive( 'has_active_subscription' )->andReturn( false );
		$this->subscription_controller->shouldReceive( 'schedule_subscription_creation' )->once();

		$this->setup_apply_cdn_mode_mocks();
		$this->setup_get_pages_data_mocks( 0, true );

		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->context->shouldReceive( 'get_rocketcdn_state' )->andReturn( Context::ROCKETCDN_STATE_ONGOING_FREE );
		$this->render_controller->shouldReceive( 'should_disable_element_for_rocketcdn' )->andReturn( false );

		$request = new \WP_REST_Request();
		$request->set_param( 'mode', Context::ROCKETCDN_FREE_TYPE );

		$response = $this->get_rest()->save_cdn_mode( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['no_pages'] === false ); // loading=true means no_pages=false
	}

	/**
	 * When mode is free and an active subscription exists, no scheduling must occur.
	 */
	public function testShouldNotScheduleCreationWhenFreeWithActiveSubscription(): void {
		$this->render_controller->shouldReceive( 'should_reject_rocketcdn_activation' )->andReturn( false );
		$this->subscription_controller->shouldReceive( 'has_active_subscription' )->andReturn( true );
		$this->subscription_controller->shouldNotReceive( 'schedule_subscription_creation' );

		$this->setup_apply_cdn_mode_mocks();
		$this->setup_get_pages_data_mocks( 0, false );

		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->context->shouldReceive( 'get_rocketcdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->render_controller->shouldReceive( 'should_disable_element_for_rocketcdn' )->andReturn( false );

		$request = new \WP_REST_Request();
		$request->set_param( 'mode', Context::ROCKETCDN_FREE_TYPE );

		$response = $this->get_rest()->save_cdn_mode( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['no_pages'] ); // no pages, not loading
	}
}
