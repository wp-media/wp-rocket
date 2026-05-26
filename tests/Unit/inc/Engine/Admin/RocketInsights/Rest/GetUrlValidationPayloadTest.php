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
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
 *
 * @group RocketInsights
 * @group Admin
 */
class GetUrlValidationPayloadTest extends TestCase {

	/**
	 * Mocked Manager instance.
	 *
	 * @var Manager|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $manager;

	/**
	 * Mocked Context instance.
	 *
	 * @var Context|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $context;

	/**
	 * Testable REST instance.
	 *
	 * @var TestableRest
	 */
	private $rest;

	/**
	 * Sets up the test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Admin/RocketInsights/Rest/TestableRest.php';

		$query         = $this->createMock( Query::class );
		$this->manager = $this->createMock( Manager::class );
		$this->context = $this->createMock( Context::class );
		$global_score  = $this->createMock( GlobalScore::class );
		$render        = $this->createMock( Render::class );
		$plan          = $this->createMock( Plan::class );
		$job_processor = $this->createMock( JobProcessor::class );
		$queue         = $this->createMock( Queue::class );

		$this->rest = new TestableRest(
			$query,
			$this->manager,
			$this->context,
			$global_score,
			$render,
			$plan,
			$job_processor,
			$queue
		);
	}

	/**
	 * Asserts that a local environment URL returns an error with a non-empty message.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnErrorForLocalEnvironment(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'local' );

		$payload = $this->rest->expose_get_url_validation_payload( 'https://example.org' );

		$this->assertTrue( $payload['error'] );
		$this->assertNotEmpty( $payload['message'], 'Local environment error message must not be empty' );
	}

	/**
	 * Asserts that a disallowed context returns an error with a non-empty message.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnErrorWhenContextDisallowed(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );

		$this->context->method( 'is_allowed' )->willReturn( false );

		$payload = $this->rest->expose_get_url_validation_payload( 'https://example.org' );

		$this->assertTrue( $payload['error'] );
		$this->assertNotEmpty( $payload['message'], 'Context-disallowed error message must not be empty' );
	}

	/**
	 * Asserts that an empty URL returns an error with a non-empty message.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnErrorForEmptyUrl(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );

		$this->context->method( 'is_allowed' )->willReturn( true );

		$payload = $this->rest->expose_get_url_validation_payload( '' );

		$this->assertTrue( $payload['error'] );
		$this->assertNotEmpty( $payload['message'], 'Empty URL error message must not be empty' );
	}

	/**
	 * Asserts that a URL returning a non-200 response gets an error with a non-empty message.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnErrorForUnreachableUrl(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( 'rocket_add_url_protocol' )->alias(
			static function ( $url ) {
				if ( strpos( $url, 'http' ) === 0 ) {
					return $url;
				}
				return 'https://' . $url;
			}
		);
		Functions\when( 'wp_safe_remote_get' )->justReturn(
			[
				'response' => [
					'code'    => 404,
					'message' => 'Not Found',
				],
				'body'     => '',
			]
		);
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 404 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );

		$this->context->method( 'is_allowed' )->willReturn( true );

		$payload = $this->rest->expose_get_url_validation_payload( 'https://unreachable.example.org' );

		$this->assertTrue( $payload['error'] );
		$this->assertNotEmpty( $payload['message'], 'Unreachable URL error message must not be empty' );
	}

	/**
	 * Asserts that a duplicate URL returns an error with a non-empty, descriptive message.
	 *
	 * This test covers the specific bug fixed in issue #8229: the duplicate-URL branch
	 * was returning error=true without a message, causing silent failure in the UI.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnErrorWithMessageForDuplicateUrl(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( 'rocket_add_url_protocol' )->alias(
			static function ( $url ) {
				if ( strpos( $url, 'http' ) === 0 ) {
					return $url;
				}
				return 'https://' . $url;
			}
		);
		Functions\when( 'wp_safe_remote_get' )->justReturn(
			[
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body'     => '<html><head><title>Test</title></head><body>content</body></html>',
			]
		);
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn(
			'<html><head><title>Test</title></head><body>content</body></html>'
		);
		Functions\when( 'admin_url' )->justReturn( 'https://example.org/wp-admin/' );

		$this->context->method( 'is_allowed' )->willReturn( true );

		// Simulate URL already submitted.
		$this->manager
			->method( 'get_single_job' )
			->willReturn(
				(object) [
					'id'  => 1,
					'url' => 'https://example.org',
				]
			);

		$payload = $this->rest->expose_get_url_validation_payload( 'https://example.org' );

		$this->assertTrue( $payload['error'] );
		$this->assertNotEmpty( $payload['message'], 'Duplicate URL must return a non-empty error message' );
		$this->assertStringContainsString(
			'already',
			$payload['message'],
			'Duplicate URL message should reference the already-monitored state'
		);
	}

	/**
	 * Asserts that a valid, reachable, non-duplicate URL returns a success payload.
	 *
	 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Rest::get_url_validation_payload
	 *
	 * @return void
	 */
	public function testShouldReturnSuccessPayloadForValidUrl(): void {
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
		Functions\when( 'rocket_add_url_protocol' )->alias(
			static function ( $url ) {
				if ( strpos( $url, 'http' ) === 0 ) {
					return $url;
				}
				return 'https://' . $url;
			}
		);
		Functions\when( 'wp_safe_remote_get' )->justReturn(
			[
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body'     => '<html><head><title>Test</title></head><body>content</body></html>',
			]
		);
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn(
			'<html><head><title>Test</title></head><body>content</body></html>'
		);
		Functions\when( 'admin_url' )->justReturn( 'https://example.org/wp-admin/' );

		$this->context->method( 'is_allowed' )->willReturn( true );

		$this->manager
			->method( 'get_single_job' )
			->willReturn( false );

		$payload = $this->rest->expose_get_url_validation_payload( 'https://example.org/valid-page' );

		$this->assertFalse( $payload['error'] );
		$this->assertNotEmpty( $payload['processed_url'] );
	}
}
