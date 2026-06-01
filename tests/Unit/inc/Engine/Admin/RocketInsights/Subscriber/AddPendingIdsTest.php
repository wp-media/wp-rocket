<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Admin\RocketInsights\Queue\Queue;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Rest as RecommendationsRest;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
 *
 * @group RocketInsights
 */
class AddPendingIdsTest extends TestCase {
	/**
	 * Instance under test.
	 *
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		if ( ! defined( 'WP_ROCKET_ASSETS_IMG_URL' ) ) {
			define( 'WP_ROCKET_ASSETS_IMG_URL', 'https://example.com/img/' );
		}

		$render               = $this->createMock( Render::class );
		$controller           = $this->createMock( Controller::class );
		$rest                 = $this->createMock( Rest::class );
		$queue                = $this->createMock( Queue::class );
		$context              = $this->createMock( Context::class );
		$global_score         = $this->createMock( GlobalScore::class );
		$options              = $this->createMock( Options_Data::class );
		$manager              = $this->createMock( Manager::class );
		$plan                 = $this->createMock( Plan::class );
		$renewal              = $this->createMock( Renewal::class );
		$recommendations_rest = $this->createMock( RecommendationsRest::class );

		$context->method( 'is_allowed' )->willReturn( true );
		$controller->method( 'get_not_finished_ids' )->willReturn( [] );
		$controller->method( 'get_global_score' )->willReturn(
			[
				'score'     => 0,
				'pages_num' => 0,
				'status'    => '',
			]
		);
		$controller->method( 'get_remaining_url_count' )->willReturn( 3 );
		$render->method( 'get_score_color_status' )->willReturn( '' );
		$render->method( 'get_global_score_widget_content' )->willReturn( '' );
		$render->method( 'get_global_score_row' )->willReturn( '' );

		$this->subscriber = new Subscriber(
			$render,
			$controller,
			$rest,
			$queue,
			$context,
			$global_score,
			$options,
			$manager,
			$plan,
			$renewal,
			$recommendations_rest
		);
	}

	/**
	 * Test that rocket_insights_errors key is present in the result.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
	 */
	public function testShouldIncludeRocketInsightsErrorsKey() {
		Functions\when( '__' )->returnArg( 1 );

		$result = $this->subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'rocket_insights_errors', $result );
	}

	/**
	 * Test that invalid_url error string is present in rocket_insights_errors.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
	 */
	public function testShouldIncludeInvalidUrlErrorString() {
		Functions\when( '__' )->returnArg( 1 );

		$result = $this->subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'invalid_url', $result['rocket_insights_errors'] );
		$this->assertNotEmpty( $result['rocket_insights_errors']['invalid_url'] );
	}

	/**
	 * Test that generic_error string is present in rocket_insights_errors.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
	 */
	public function testShouldIncludeGenericErrorString() {
		Functions\when( '__' )->returnArg( 1 );

		$result = $this->subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'generic_error', $result['rocket_insights_errors'] );
		$this->assertNotEmpty( $result['rocket_insights_errors']['generic_error'] );
	}

	/**
	 * Test that add_pending_ids returns early when context is not allowed.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
	 */
	public function testShouldReturnEarlyWhenContextNotAllowed() {
		$render               = $this->createMock( Render::class );
		$controller           = $this->createMock( Controller::class );
		$rest                 = $this->createMock( Rest::class );
		$queue                = $this->createMock( Queue::class );
		$context              = $this->createMock( Context::class );
		$global_score         = $this->createMock( GlobalScore::class );
		$options              = $this->createMock( Options_Data::class );
		$manager              = $this->createMock( Manager::class );
		$plan                 = $this->createMock( Plan::class );
		$renewal              = $this->createMock( Renewal::class );
		$recommendations_rest = $this->createMock( RecommendationsRest::class );

		$context->method( 'is_allowed' )->willReturn( false );

		$subscriber = new Subscriber(
			$render,
			$controller,
			$rest,
			$queue,
			$context,
			$global_score,
			$options,
			$manager,
			$plan,
			$renewal,
			$recommendations_rest
		);

		$input  = [ 'existing_key' => 'value' ];
		$result = $subscriber->add_pending_ids( $input );

		$this->assertSame( $input, $result );
		$this->assertArrayNotHasKey( 'rocket_insights_errors', $result );
	}
}
