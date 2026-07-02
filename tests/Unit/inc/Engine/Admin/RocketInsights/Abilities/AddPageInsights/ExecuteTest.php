<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\AddPageInsights;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\AddPageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\AddPageInsights::execute
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class ExecuteTest extends TestCase {

	/**
	 * @var Context|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $context;

	/**
	 * @var Manager|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $manager;

	/**
	 * @var JobProcessor|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $job_processor;

	/**
	 * @var Queue|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $queue;

	/**
	 * @var Query|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $query;

	/**
	 * @var Plan|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $plan;

	/**
	 * @var AddPageInsights
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->context       = $this->createMock( Context::class );
		$this->manager       = $this->createMock( Manager::class );
		$this->job_processor = $this->createMock( JobProcessor::class );
		$this->queue         = $this->createMock( Queue::class );
		$this->query         = $this->createMock( Query::class );
		$this->plan          = $this->createMock( Plan::class );

		$this->ability = new AddPageInsights(
			$this->context,
			$this->manager,
			$this->job_processor,
			$this->queue,
			$this->query,
			$this->plan
		);

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		// Stub environment type.
		Functions\when( 'wp_get_environment_type' )->justReturn( $config['environment_type'] ?? 'production' );

		// Stub URL protocol addition.
		Functions\when( 'rocket_add_url_protocol' )->alias( function ( $url ) {
			if ( empty( $url ) ) {
				return $url;
			}
			if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
				return 'https://' . $url;
			}
			return $url;
		} );

		// Stub admin_url.
		Functions\when( 'admin_url' )->justReturn( $config['admin_url'] ?? 'https://example.com/wp-admin/' );

		// Stub home_url for Utils::is_home().
		Functions\when( 'home_url' )->justReturn( $config['home_url'] ?? 'https://example.com/' );

		// Stub sanitization functions.
		Functions\when( 'wp_strip_all_tags' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();

		// Stub HTTP request functions.
		$this->stubHttpFunctions( $config );

		// Set up Context mock.
		if ( isset( $config['context_is_allowed'] ) ) {
			$this->context
				->expects( $this->any() )
				->method( 'is_allowed' )
				->willReturn( $config['context_is_allowed'] );
		}

		// Set up Manager mock.
		$this->setupManagerMock( $config, $expected );

		// Set up JobProcessor mock.
		$this->setupJobProcessorMock( $config, $expected );

		// Set up Queue mock.
		$this->setupQueueMock( $config, $expected );

		// Set up Query mock.
		$this->setupQueryMock( $config, $expected );

		// Set up Plan mock.
		$this->setupPlanMock( $config, $expected );

		// Set up action expectations.
		if ( $expected['action_fired'] ?? false ) {
			Actions\expectDone( 'rocket_rocket_insights_job_added' )
				->once()
				->with(
					$expected['action_url'],
					$expected['action_plan'],
					$expected['action_urls_count'],
					'mcp-ai'
				);
		} else {
			Actions\expectDone( 'rocket_rocket_insights_job_added' )->never();
		}

		$result = $this->ability->execute( $config['input'] );

		$this->assertSame( $expected['result'], $result );
	}

	/**
	 * Stub HTTP functions for get_page_content().
	 *
	 * @param array $config Test configuration.
	 */
	private function stubHttpFunctions( array $config ): void {
		$http_response = $config['http_response'] ?? null;

		if ( null === $http_response ) {
			Functions\when( 'wp_safe_remote_get' )->justReturn( [] );
			Functions\when( 'is_wp_error' )->justReturn( true );
			Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 0 );
			Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );
			return;
		}

		Functions\when( 'wp_safe_remote_get' )->justReturn( $http_response['response'] ?? [] );
		Functions\when( 'is_wp_error' )->justReturn( $http_response['is_error'] ?? false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( $http_response['status_code'] ?? 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( $http_response['body'] ?? '' );
	}

	/**
	 * Set up Manager mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	private function setupManagerMock( array $config, array $expected ): void {
		// get_single_job.
		if ( isset( $config['manager_get_single_job'] ) ) {
			$this->manager
				->expects( $this->any() )
				->method( 'get_single_job' )
				->willReturn( $config['manager_get_single_job'] );
		} else {
			$this->manager
				->expects( $this->any() )
				->method( 'get_single_job' )
				->willReturn( false );
		}

		// add_to_the_queue.
		if ( $expected['manager_add_to_queue_called'] ?? false ) {
			$this->manager
				->expects( $this->once() )
				->method( 'add_to_the_queue' )
				->willReturn( $config['manager_add_to_queue_result'] ?? 1 );
		} else {
			$this->manager
				->expects( $this->never() )
				->method( 'add_to_the_queue' );
		}

		// make_status_inprogress.
		if ( $expected['manager_make_status_inprogress_called'] ?? false ) {
			$this->manager
				->expects( $this->once() )
				->method( 'make_status_inprogress' );
		}
	}

	/**
	 * Set up JobProcessor mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	private function setupJobProcessorMock( array $config, array $expected ): void {
		if ( $expected['job_processor_send_api_called'] ?? false ) {
			$this->job_processor
				->expects( $this->once() )
				->method( 'send_api' )
				->willReturn( $config['job_processor_send_api_result'] ?? false );
		} else {
			$this->job_processor
				->expects( $this->never() )
				->method( 'send_api' );
		}
	}

	/**
	 * Set up Queue mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	private function setupQueueMock( array $config, array $expected ): void {
		if ( $expected['queue_schedule_called'] ?? false ) {
			$this->queue
				->expects( $this->once() )
				->method( 'schedule_job_status_single_task' );
		} else {
			$this->queue
				->expects( $this->never() )
				->method( 'schedule_job_status_single_task' );
		}
	}

	/**
	 * Set up Query mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	private function setupQueryMock( array $config, array $expected ): void {
		// get_total_count.
		if ( isset( $config['query_total_count'] ) ) {
			$this->query
				->expects( $this->any() )
				->method( 'get_total_count' )
				->willReturn( $config['query_total_count'] );
		}

		// delete_item.
		if ( $expected['query_delete_item_called'] ?? false ) {
			$this->query
				->expects( $this->once() )
				->method( 'delete_item' );
		} else {
			$this->query
				->expects( $this->never() )
				->method( 'delete_item' );
		}
	}

	/**
	 * Set up Plan mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 */
	private function setupPlanMock( array $config, array $expected ): void {
		if ( isset( $config['plan_max_urls'] ) ) {
			$this->plan
				->expects( $this->any() )
				->method( 'max_urls' )
				->willReturn( $config['plan_max_urls'] );
		}

		if ( $expected['plan_get_current_plan_called'] ?? false ) {
			$this->plan
				->expects( $this->once() )
				->method( 'get_current_plan' )
				->willReturn( $config['plan_current_plan'] ?? 'free' );
		}
	}
}
