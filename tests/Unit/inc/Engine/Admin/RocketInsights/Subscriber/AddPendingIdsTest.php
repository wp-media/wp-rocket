<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

if ( ! defined( 'WP_ROCKET_ASSETS_IMG_URL' ) ) {
	define( 'WP_ROCKET_ASSETS_IMG_URL', 'http://example.org/wp-content/plugins/wp-rocket/assets/img/' );
}

use Brain\Monkey\Functions;
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

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_pending_ids
 */
class AddPendingIdsTest extends TestCase {

	/**
	 * Creates a Subscriber instance with all required mock dependencies.
	 *
	 * @param Context $context The context mock to use.
	 * @return Subscriber
	 */
	private function makeSubscriber( Context $context ): Subscriber {
		$render               = $this->createMock( Render::class );
		$controller           = $this->createMock( Controller::class );
		$rest                 = $this->createMock( Rest::class );
		$queue                = $this->createMock( Queue::class );
		$global_score         = $this->createMock( GlobalScore::class );
		$options              = $this->createMock( Options_Data::class );
		$manager              = $this->createMock( Manager::class );
		$plan                 = $this->createMock( Plan::class );
		$renewal              = $this->createMock( Renewal::class );
		$recommendations_rest = $this->createMock( RecommendationsRest::class );

		$controller->method( 'get_not_finished_ids' )->willReturn( [] );
		$controller->method( 'get_global_score' )->willReturn(
			[
				'score'     => 0,
				'pages_num' => 0,
				'status'    => 'none',
			]
		);
		$controller->method( 'get_remaining_url_count' )->willReturn( 3 );

		$global_score->method( 'get_global_score_data' )->willReturn(
			[
				'score'     => 0,
				'pages_num' => 0,
				'status'    => 'none',
			]
		);

		$render->method( 'get_score_color_status' )->willReturn( 'grey' );
		$render->method( 'get_global_score_widget_content' )->willReturn( '' );
		$render->method( 'get_global_score_row' )->willReturn( '' );

		return new Subscriber(
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
	 * Tests that add_pending_ids returns the data unchanged when context is not allowed.
	 */
	public function testShouldReturnDataUnchangedWhenContextNotAllowed(): void {
		$context = $this->createMock( Context::class );
		$context->method( 'is_allowed' )->willReturn( false );

		$subscriber = $this->makeSubscriber( $context );
		$result     = $subscriber->add_pending_ids( [] );

		$this->assertSame( [], $result );
	}

	/**
	 * Tests that add_pending_ids includes rocket_insights_errors when context is allowed.
	 */
	public function testShouldIncludeRocketInsightsErrorsWhenContextAllowed(): void {
		Functions\stubTranslationFunctions();

		$context = $this->createMock( Context::class );
		$context->method( 'is_allowed' )->willReturn( true );

		$subscriber = $this->makeSubscriber( $context );
		$result     = $subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'rocket_insights_errors', $result );
	}

	/**
	 * Tests that rocket_insights_errors contains invalid_url key.
	 */
	public function testShouldIncludeInvalidUrlKeyInErrors(): void {
		Functions\stubTranslationFunctions();

		$context = $this->createMock( Context::class );
		$context->method( 'is_allowed' )->willReturn( true );

		$subscriber = $this->makeSubscriber( $context );
		$result     = $subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'invalid_url', $result['rocket_insights_errors'] );
		$this->assertNotEmpty( $result['rocket_insights_errors']['invalid_url'] );
	}

	/**
	 * Tests that rocket_insights_errors contains generic_error key.
	 */
	public function testShouldIncludeGenericErrorKeyInErrors(): void {
		Functions\stubTranslationFunctions();

		$context = $this->createMock( Context::class );
		$context->method( 'is_allowed' )->willReturn( true );

		$subscriber = $this->makeSubscriber( $context );
		$result     = $subscriber->add_pending_ids( [] );

		$this->assertArrayHasKey( 'generic_error', $result['rocket_insights_errors'] );
		$this->assertNotEmpty( $result['rocket_insights_errors']['generic_error'] );
	}
}
