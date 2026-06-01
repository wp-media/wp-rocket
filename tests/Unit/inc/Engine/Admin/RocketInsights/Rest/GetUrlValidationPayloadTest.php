<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Rest;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
 *
 * @group Admin
 * @group RocketInsights
 */
class GetUrlValidationPayloadTest extends TestCase {

	/**
	 * REST controller mock.
	 *
	 * @var Rest&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $rest;

	/**
	 * Context mock.
	 *
	 * @var Context&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $context;

	/**
	 * Manager mock.
	 *
	 * @var Manager&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $manager;

	/**
	 * Sets up the test with mocked dependencies.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->context = $this->createMock( Context::class );
		$this->manager = $this->createMock( Manager::class );

		$query         = $this->createMock( Query::class );
		$global_score  = $this->createMock( GlobalScore::class );
		$render        = $this->createMock( Render::class );
		$plan          = $this->createMock( Plan::class );
		$job_processor = $this->createMock( JobProcessor::class );
		$queue         = $this->createMock( Queue::class );

		// Partial mock: mock get_page_content so we control URL reachability.
		$this->rest = $this->getMockBuilder( Rest::class )
			->setConstructorArgs(
				[
					$query,
					$this->manager,
					$this->context,
					$global_score,
					$render,
					$plan,
					$job_processor,
					$queue,
				]
			)
			->onlyMethods( [ 'get_page_content' ] )
			->getMock();
	}

	/**
	 * Calls the protected get_url_validation_payload method via Reflection.
	 *
	 * @param string $url The URL to validate.
	 * @return array
	 */
	private function callGetUrlValidationPayload( string $url ): array {
		$method = new \ReflectionMethod( $this->rest, 'get_url_validation_payload' );
		$method->setAccessible( true );
		return $method->invoke( $this->rest, $url );
	}

	/**
	 * Tests that a local environment returns error_code 'local_environment'.
	 *
	 * @return void
	 */
	public function testLocalEnvironmentReturnsLocalEnvironmentErrorCode(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'local' );
		Functions\when( '__' )->returnArg();

		$result = $this->callGetUrlValidationPayload( 'https://example.com' );

		$this->assertTrue( $result['error'] );
		$this->assertSame( 'local_environment', $result['error_code'] );
		$this->assertNotEmpty( $result['message'] );
	}

	/**
	 * Tests that an unreachable URL returns error_code 'url_not_accessible'.
	 *
	 * @return void
	 */
	public function testUnreachableUrlReturnsUrlNotAccessibleErrorCode(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_add_url_protocol' )->returnArg();

		$this->context
			->expects( $this->once() )
			->method( 'is_allowed' )
			->willReturn( true );

		$this->rest
			->expects( $this->once() )
			->method( 'get_page_content' )
			->willReturn( false );

		$result = $this->callGetUrlValidationPayload( 'https://not-accessible.example' );

		$this->assertTrue( $result['error'] );
		$this->assertSame( 'url_not_accessible', $result['error_code'] );
		$this->assertNotEmpty( $result['message'] );
	}

	/**
	 * Tests that an admin URL returns error_code 'admin_url'.
	 *
	 * @return void
	 */
	public function testAdminUrlReturnsAdminUrlErrorCode(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'admin_url' )->justReturn( 'https://example.com/wp-admin/' );

		$this->context
			->expects( $this->once() )
			->method( 'is_allowed' )
			->willReturn( true );

		$this->rest
			->expects( $this->once() )
			->method( 'get_page_content' )
			->willReturn( '<html><title>Admin</title></html>' );

		$result = $this->callGetUrlValidationPayload( 'https://example.com/wp-admin/' );

		$this->assertTrue( $result['error'] );
		$this->assertSame( 'admin_url', $result['error_code'] );
		$this->assertNotEmpty( $result['message'] );
	}

	/**
	 * Tests that an already-analyzed URL returns error_code 'url_already_analyzed'.
	 *
	 * @return void
	 */
	public function testAlreadyAnalyzedUrlReturnsUrlAlreadyAnalyzedErrorCode(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'admin_url' )->justReturn( 'https://example.com/wp-admin/' );

		$this->context
			->expects( $this->once() )
			->method( 'is_allowed' )
			->willReturn( true );

		$this->rest
			->expects( $this->once() )
			->method( 'get_page_content' )
			->willReturn( '<html><title>Test Page</title></html>' );

		$this->manager
			->expects( $this->once() )
			->method( 'get_single_job' )
			->willReturn( (object) [ 'id' => 1 ] );

		$result = $this->callGetUrlValidationPayload( 'https://example.com/test-page' );

		$this->assertTrue( $result['error'] );
		$this->assertSame( 'url_already_analyzed', $result['error_code'] );
		$this->assertNotEmpty( $result['message'] );
	}

	/**
	 * Tests that a valid, new URL returns a success payload with no error.
	 *
	 * @return void
	 */
	public function testValidUrlReturnsSuccessPayload(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'admin_url' )->justReturn( 'https://example.com/wp-admin/' );

		$this->context
			->expects( $this->once() )
			->method( 'is_allowed' )
			->willReturn( true );

		$html = '<html><title>My Page</title></html>';

		$this->rest
			->expects( $this->once() )
			->method( 'get_page_content' )
			->willReturn( $html );

		$this->manager
			->expects( $this->once() )
			->method( 'get_single_job' )
			->willReturn( false );

		$result = $this->callGetUrlValidationPayload( 'https://example.com/my-page' );

		$this->assertFalse( $result['error'] );
		$this->assertSame( '', $result['error_code'] );
		$this->assertSame( $html, $result['message'] );
		$this->assertSame( 'https://example.com/my-page', $result['processed_url'] );
	}
}
