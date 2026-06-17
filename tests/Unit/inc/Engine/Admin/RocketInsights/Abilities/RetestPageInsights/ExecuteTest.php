<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\RetestPageInsights;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\RetestPageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights as RocketInsightsRow;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\RetestPageInsights::execute
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
	 * @var RetestPageInsights
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->context       = $this->createMock( Context::class );
		$this->manager       = $this->createMock( Manager::class );
		$this->job_processor = $this->createMock( JobProcessor::class );
		$this->queue         = $this->createMock( Queue::class );
		$this->query         = $this->createMock( Query::class );

		$this->ability = new RetestPageInsights(
			$this->context,
			$this->manager,
			$this->job_processor,
			$this->queue,
			$this->query
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

		// Set up Context mock.
		if ( isset( $config['context_is_allowed'] ) ) {
			$this->context
				->expects( $this->any() )
				->method( 'is_allowed' )
				->willReturn( $config['context_is_allowed'] );
		}

		// Build row mock if needed.
		$row = null;
		if ( isset( $config['row_is_running'] ) || isset( $config['row_id'] ) ) {
			$row = $this->getMockBuilder( RocketInsightsRow::class )
				->disableOriginalConstructor()
				->getMock();
			$row->id = $config['row_id'] ?? 42;
			if ( isset( $config['row_is_running'] ) ) {
				$row->expects( $this->any() )
					->method( 'is_running' )
					->willReturn( $config['row_is_running'] );
			}
		}

		// Set up Manager mock.
		$this->setupManagerMock( $config, $expected, $row );

		// Set up JobProcessor mock.
		$this->setupJobProcessorMock( $config, $expected );

		// Set up Queue mock.
		$this->setupQueueMock( $config, $expected );

		// Set up action expectations.
		if ( $expected['action_fired'] ?? false ) {
			Actions\expectDone( 'rocket_rocket_insights_job_retest' )
				->once()
				->with( $expected['action_row_id'] ?? 42 );
		} else {
			Actions\expectDone( 'rocket_rocket_insights_job_retest' )->never();
		}

		$result = $this->ability->execute( $config['input'] );

		$this->assertSame( $expected['result'], $result );
	}

	/**
	 * Set up Manager mock expectations.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 * @param mixed $row      The row mock object or null.
	 */
	private function setupManagerMock( array $config, array $expected, $row ): void {
		// get_single_job.
		if ( array_key_exists( 'manager_get_single_job', $config ) ) {
			// Explicit false/empty value — URL not tracked.
			$this->manager
				->expects( $this->any() )
				->method( 'get_single_job' )
				->willReturn( $config['manager_get_single_job'] );
		} elseif ( null !== $row ) {
			// Return the row mock.
			$this->manager
				->expects( $this->any() )
				->method( 'get_single_job' )
				->willReturn( $row );
		} else {
			$this->manager
				->expects( $this->any() )
				->method( 'get_single_job' )
				->willReturn( false );
		}

		// add_to_the_queue — verify $additional_details contains stale-score reset fields when asserted.
		if ( $expected['manager_add_to_queue_called'] ?? false ) {
			$queue_result = $config['manager_add_to_queue_result'] ?? 42;
			if ( $expected['assert_additional_details'] ?? false ) {
				$this->manager
					->expects( $this->once() )
					->method( 'add_to_the_queue' )
					->with(
						$this->anything(),
						$this->anything(),
						$this->callback( function ( $details ) {
							return isset( $details['score'] )
								&& '' === $details['score']
								&& isset( $details['report_url'] )
								&& '' === $details['report_url']
								&& isset( $details['is_blurred'] )
								&& 0 === $details['is_blurred'];
						} )
					)
					->willReturn( $queue_result );
			} else {
				$this->manager
					->expects( $this->once() )
					->method( 'add_to_the_queue' )
					->willReturn( $queue_result );
			}
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
}
