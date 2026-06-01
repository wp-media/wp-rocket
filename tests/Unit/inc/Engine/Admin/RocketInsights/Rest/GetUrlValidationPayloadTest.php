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
 * @group RocketInsights
 * @group Admin
 */
class GetUrlValidationPayloadTest extends TestCase {

	/**
	 * Sets up the test environment.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();
		$this->stubTranslationFunctions();
	}

	/**
	 * Creates a testable Rest subclass that exposes the protected method
	 * and overrides get_page_content() to avoid real HTTP calls.
	 *
	 * @param Manager $manager          Jobs manager mock.
	 * @param Context $context          Context mock.
	 * @param mixed   $page_content_val Return value for get_page_content(): string HTML or false.
	 * @return Rest
	 */
	private function makeRest( Manager $manager, Context $context, $page_content_val = '' ): Rest {
		return new class(
			$this->createMock( Query::class ),
			$manager,
			$context,
			$this->createMock( GlobalScore::class ),
			$this->createMock( Render::class ),
			$this->createMock( Plan::class ),
			$this->createMock( JobProcessor::class ),
			$this->createMock( Queue::class ),
			$page_content_val
		) extends Rest {
			/**
			 * Stubbed page content return value.
			 *
			 * @var string|false
			 */
			private $page_content_stub;

			/**
			 * Constructor.
			 *
			 * @param Query        $query            Query instance.
			 * @param Manager      $manager          Manager instance.
			 * @param Context      $context          Context instance.
			 * @param GlobalScore  $global_score     GlobalScore instance.
			 * @param Render       $render           Render instance.
			 * @param Plan         $plan             Plan instance.
			 * @param JobProcessor $job_processor    JobProcessor instance.
			 * @param Queue        $queue            Queue instance.
			 * @param mixed        $page_content_val Stub value for get_page_content().
			 */
			public function __construct(
				Query $query,
				Manager $manager,
				Context $context,
				GlobalScore $global_score,
				Render $render,
				Plan $plan,
				JobProcessor $job_processor,
				Queue $queue,
				$page_content_val
			) {
				parent::__construct( $query, $manager, $context, $global_score, $render, $plan, $job_processor, $queue );
				$this->page_content_stub = $page_content_val;
			}

			/**
			 * Override HTTP call with a stub.
			 *
			 * @param string $url The URL to fetch.
			 * @return string|false
			 */
			public function get_page_content( string $url ) {
				return $this->page_content_stub;
			}

			/**
			 * Exposes the protected get_url_validation_payload() for testing.
			 *
			 * @param string $url The URL to validate.
			 * @return array
			 */
			public function expose_get_url_validation_payload( string $url ): array {
				return $this->get_url_validation_payload( $url );
			}
		};
	}

	/**
	 * Tests all error cases and the success case for get_url_validation_payload().
	 *
	 * @param string      $scenario                Identifier string for the test scenario.
	 * @param callable    $setup                   Callback to set up Brain\Monkey stubs.
	 * @param string      $url                     URL to pass to the method under test.
	 * @param bool        $expected_error          Expected value of $payload['error'].
	 * @param string|null $expected_msg_fragment   Expected substring in $payload['message'], or null to skip check.
	 * @param string|null $expected_processed_url  Expected $payload['processed_url'], or null to skip check.
	 *
	 * @dataProvider providerUrlValidationCases
	 */
	public function testGetUrlValidationPayload(
		string $scenario,
		callable $setup,
		string $url,
		bool $expected_error,
		?string $expected_msg_fragment,
		?string $expected_processed_url
	): void {
		$setup();

		[ $rest ] = $this->buildRestForScenario( $scenario, $url );

		// @phpstan-ignore-next-line
		$payload = $rest->expose_get_url_validation_payload( $url );

		$this->assertSame( $expected_error, $payload['error'], "error flag mismatch for scenario: {$scenario}" );

		if ( null !== $expected_msg_fragment ) {
			$this->assertStringContainsString(
				$expected_msg_fragment,
				$payload['message'],
				"message mismatch for scenario: {$scenario}"
			);
		}

		if ( null !== $expected_processed_url ) {
			$this->assertSame( $expected_processed_url, $payload['processed_url'], "processed_url mismatch for scenario: {$scenario}" );
		}
	}

	/**
	 * Builds a configured Rest test double for a given scenario.
	 *
	 * @param string $scenario Identifier string for the test scenario.
	 * @param string $url      URL under test.
	 * @return array{0: Rest}
	 */
	private function buildRestForScenario( string $scenario, string $url ): array {
		$context = $this->createMock( Context::class );
		$manager = $this->createMock( Manager::class );

		switch ( $scenario ) {
			case 'local_environment':
				$rest = $this->makeRest( $manager, $context, false );
				break;

			case 'context_not_allowed':
				$context->method( 'is_allowed' )->willReturn( false );
				$rest = $this->makeRest( $manager, $context, false );
				break;

			case 'empty_url':
				$context->method( 'is_allowed' )->willReturn( true );
				$rest = $this->makeRest( $manager, $context, '<html></html>' );
				break;

			case 'unreachable_url':
				$context->method( 'is_allowed' )->willReturn( true );
				$rest = $this->makeRest( $manager, $context, false );
				break;

			case 'admin_url':
				$context->method( 'is_allowed' )->willReturn( true );
				$rest = $this->makeRest( $manager, $context, '<html><title>Admin</title></html>' );
				break;

			case 'already_analyzed':
				$context->method( 'is_allowed' )->willReturn( true );
				$manager->method( 'get_single_job' )->willReturn( (object) [ 'id' => 1 ] );
				$rest = $this->makeRest( $manager, $context, '<html><title>Page</title></html>' );
				break;

			case 'success':
			default:
				$context->method( 'is_allowed' )->willReturn( true );
				$manager->method( 'get_single_job' )->willReturn( false );
				$rest = $this->makeRest( $manager, $context, '<html><title>My Page</title></html>' );
				break;
		}

		return [ $rest ];
	}

	/**
	 * Data provider for testGetUrlValidationPayload.
	 *
	 * @return array
	 */
	public function providerUrlValidationCases(): array {
		return [
			'local environment returns error with translated message' => [
				'scenario'               => 'local_environment',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'local' );
				},
				'url'                    => 'http://example.local/page',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'local environments',
				'expected_processed_url' => null,
			],
			'context not allowed returns error with translated message' => [
				'scenario'               => 'context_not_allowed',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
				},
				'url'                    => 'http://example.org/page',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'currently disabled',
				'expected_processed_url' => null,
			],
			'empty url returns error with translated message' => [
				'scenario'               => 'empty_url',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
				},
				'url'                    => '',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'Please enter a URL',
				'expected_processed_url' => null,
			],
			'unreachable url returns error with translated message' => [
				'scenario'               => 'unreachable_url',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
					Functions\when( 'rocket_add_url_protocol' )->returnArg();
				},
				'url'                    => 'http://unreachable.example.com/page',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'publicly accessible',
				'expected_processed_url' => null,
			],
			'admin url returns error with translated message' => [
				'scenario'               => 'admin_url',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
					Functions\when( 'rocket_add_url_protocol' )->returnArg();
					Functions\when( 'admin_url' )->justReturn( 'http://example.org/wp-admin/' );
				},
				'url'                    => 'http://example.org/wp-admin/edit.php',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'Admin pages cannot be monitored',
				'expected_processed_url' => null,
			],
			'already analyzed url returns error with translated message' => [
				'scenario'               => 'already_analyzed',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
					Functions\when( 'rocket_add_url_protocol' )->returnArg();
					Functions\when( 'admin_url' )->justReturn( 'http://example.org/wp-admin/' );
				},
				'url'                    => 'http://example.org/existing-page',
				'expected_error'         => true,
				'expected_msg_fragment'  => 'already being monitored',
				'expected_processed_url' => null,
			],
			'valid url succeeds with no error' => [
				'scenario'               => 'success',
				'setup'                  => function () {
					Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
					Functions\when( 'rocket_add_url_protocol' )->returnArg();
					Functions\when( 'admin_url' )->justReturn( 'http://example.org/wp-admin/' );
				},
				'url'                    => 'http://example.org/new-page',
				'expected_error'         => false,
				'expected_msg_fragment'  => null,
				'expected_processed_url' => 'http://example.org/new-page',
			],
		];
	}
}
