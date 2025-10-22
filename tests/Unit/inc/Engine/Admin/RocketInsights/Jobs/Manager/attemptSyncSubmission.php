<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Jobs\Manager;

use Mockery;
use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager::attempt_sync_submission
 *
 * @group RocketInsights
 */
class Test_AttemptSyncSubmission extends TestCase {
	use HasLoggerTrait;

	/**
	 * Query mock.
	 *
	 * @var Mockery\MockInterface|RocketInsightsQuery
	 */
	protected $query;

	/**
	 * Context mock.
	 *
	 * @var Mockery\MockInterface|ContextInterface
	 */
	protected $context;

	/**
	 * Plan mock.
	 *
	 * @var Mockery\MockInterface|Plan
	 */
	protected $plan;

	/**
	 * API Client mock.
	 *
	 * @var Mockery\MockInterface|APIClient
	 */
	protected $api_client;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->query      = $this->createMock( RocketInsightsQuery::class );
		$this->context    = Mockery::mock( ContextInterface::class );
		$this->plan       = Mockery::mock( Plan::class );
		$this->api_client = Mockery::mock( APIClient::class );

		$this->manager = new Manager( $this->query, $this->context, $this->plan, $this->api_client );
		$this->set_logger( $this->manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->api_client
			->expects()
			->add_to_queue( $config['url'], $config['options'], $config['timeout_args'] )
			->once()
			->andReturn( $config['response'] );

		$result = $this->manager->attempt_sync_submission( $config['url'], $config['is_mobile'], $config['additional_details'] );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertEquals( $expected['error_code'], $result->get_error_code() );
			if ( isset( $expected['error_message'] ) ) {
				$this->assertStringContainsString( $expected['error_message'], $result->get_error_message() );
			}
		} else {
			$this->assertIsArray( $result );
			$this->assertArrayHasKey( 'uuid', $result );
			$this->assertEquals( $expected['uuid'], $result['uuid'] );
		}
	}

	/**
	 * Test that custom timeout is passed to API client.
	 */
	public function testShouldPassCorrectTimeoutToApiClient() {
		$url = 'https://example.com';
		
		$this->api_client
			->expects()
			->add_to_queue(
				$url,
				[ 'is_home' => false ],
				[ 'timeout' => Manager::SYNC_TIMEOUT ]
			)
			->once()
			->andReturn( [ 'uuid' => 'test-uuid-123' ] );

		$result = $this->manager->attempt_sync_submission( $url, false );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'uuid', $result );
	}

	/**
	 * Test that is_home option is passed from additional_details.
	 */
	public function testShouldPassIsHomeFromAdditionalDetails() {
		$url = 'https://example.com';
		$additional_details = [ 'is_home' => true ];
		
		$this->api_client
			->expects()
			->add_to_queue(
				$url,
				[ 'is_home' => true ],
				[ 'timeout' => Manager::SYNC_TIMEOUT ]
			)
			->once()
			->andReturn( [ 'uuid' => 'test-uuid-homepage' ] );

		$result = $this->manager->attempt_sync_submission( $url, false, $additional_details );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'uuid', $result );
	}

	/**
	 * Test that WP_Error is returned when API returns error.
	 */
	public function testShouldReturnWpErrorWhenApiReturnsError() {
		$url = 'https://example.com';
		$wp_error = new WP_Error( 'api_error', 'API request failed' );
		
		$this->api_client
			->expects()
			->add_to_queue(
				$url,
				[ 'is_home' => false ],
				[ 'timeout' => Manager::SYNC_TIMEOUT ]
			)
			->once()
			->andReturn( $wp_error );

		$result = $this->manager->attempt_sync_submission( $url, false );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'api_error', $result->get_error_code() );
	}

	/**
	 * Test that WP_Error is created when no UUID is returned.
	 */
	public function testShouldReturnWpErrorWhenNoUuidReturned() {
		$url = 'https://example.com';
		
		$this->api_client
			->expects()
			->add_to_queue(
				$url,
				[ 'is_home' => false ],
				[ 'timeout' => Manager::SYNC_TIMEOUT ]
			)
			->once()
			->andReturn( [ 'code' => 200, 'message' => 'OK' ] );

		$result = $this->manager->attempt_sync_submission( $url, false );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'sync_submission_failed', $result->get_error_code() );
		$this->assertEquals( 'No UUID returned', $result->get_error_message() );
	}

	/**
	 * Test successful sync submission returns response array.
	 */
	public function testShouldReturnResponseOnSuccess() {
		$url = 'https://example.com';
		$response = [
			'uuid' => 'test-uuid-success',
			'code' => 200,
		];
		
		$this->api_client
			->expects()
			->add_to_queue(
				$url,
				[ 'is_home' => false ],
				[ 'timeout' => Manager::SYNC_TIMEOUT ]
			)
			->once()
			->andReturn( $response );

		$result = $this->manager->attempt_sync_submission( $url, false );

		$this->assertIsArray( $result );
		$this->assertSame( $response, $result );
	}
}
