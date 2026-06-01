<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Rest;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
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
 */
class GetUrlValidationPayloadTest extends TestCase {

	/**
	 * Context mock.
	 *
	 * @var Context&Mockery\MockInterface
	 */
	private $context;

	/**
	 * Manager mock.
	 *
	 * @var Manager&Mockery\MockInterface
	 */
	private $manager;

	/**
	 * Reflected method for calling the protected method under test.
	 *
	 * @var ReflectionMethod
	 */
	private $method;

	/**
	 * Rest partial mock instance.
	 *
	 * @var Rest&Mockery\MockInterface
	 */
	private $rest;

	protected function setUp(): void {
		parent::setUp();

		$this->context = Mockery::mock( Context::class );
		$this->manager = Mockery::mock( Manager::class );

		$query = $this->createMock( Query::class );

		$this->rest = Mockery::mock(
			Rest::class . '[get_page_content]',
			[
				$query,
				$this->manager,
				$this->context,
				Mockery::mock( GlobalScore::class ),
				Mockery::mock( Render::class ),
				Mockery::mock( Plan::class ),
				Mockery::mock( JobProcessor::class ),
				Mockery::mock( Queue::class ),
			]
		)->makePartial()->shouldAllowMockingProtectedMethods();

		$this->method = new ReflectionMethod( Rest::class, 'get_url_validation_payload' );
		$this->method->setAccessible( true );
	}

	private function callMethod( string $url ): array {
		return $this->method->invoke( $this->rest, $url );
	}

	/**
	 * Tests that a URL already being monitored returns a meaningful translatable error message.
	 * This covers the previously broken branch that set $payload['error'] = true
	 * but never set $payload['message'].
	 */
	public function testShouldReturnErrorWithMessageForAlreadyMonitoredUrl(): void {
		Functions\expect( 'wp_get_environment_type' )
			->once()
			->andReturn( 'production' );

		$this->context->shouldReceive( 'is_allowed' )
			->once()
			->andReturn( true );

		Functions\expect( 'rocket_add_url_protocol' )
			->once()
			->andReturn( 'https://example.com' );

		$this->rest->shouldReceive( 'get_page_content' )
			->once()
			->andReturn( '<html><title>Example</title></html>' );

		Functions\expect( 'admin_url' )
			->once()
			->andReturn( 'https://example.com/wp-admin/' );

		$this->manager->shouldReceive( 'get_single_job' )
			->once()
			->with( 'https://example.com', true )
			->andReturn( (object) [ 'id' => 1 ] );

		Functions\expect( '__' )
			->with( 'This URL is already being monitored.', 'rocket' )
			->andReturn( 'This URL is already being monitored.' );

		$payload = $this->callMethod( 'https://example.com' );

		$this->assertTrue( $payload['error'] );
		$this->assertSame( 'This URL is already being monitored.', $payload['message'] );
	}

	/**
	 * Tests that an inaccessible URL returns a translatable error message.
	 */
	public function testShouldReturnErrorForInaccessibleUrl(): void {
		Functions\expect( 'wp_get_environment_type' )
			->once()
			->andReturn( 'production' );

		$this->context->shouldReceive( 'is_allowed' )
			->once()
			->andReturn( true );

		Functions\expect( 'rocket_add_url_protocol' )
			->once()
			->andReturn( 'https://example.com' );

		$this->rest->shouldReceive( 'get_page_content' )
			->once()
			->andReturn( false );

		Functions\expect( '__' )
			->with( 'Url does not resolve to a valid page.', 'rocket' )
			->andReturn( 'Url does not resolve to a valid page.' );

		$payload = $this->callMethod( 'https://example.com' );

		$this->assertTrue( $payload['error'] );
		$this->assertSame( 'Url does not resolve to a valid page.', $payload['message'] );
	}

	/**
	 * Tests that an admin URL returns a translatable error message.
	 */
	public function testShouldReturnErrorForAdminUrl(): void {
		Functions\expect( 'wp_get_environment_type' )
			->once()
			->andReturn( 'production' );

		$this->context->shouldReceive( 'is_allowed' )
			->once()
			->andReturn( true );

		Functions\expect( 'rocket_add_url_protocol' )
			->once()
			->andReturn( 'https://example.com/wp-admin/' );

		$this->rest->shouldReceive( 'get_page_content' )
			->once()
			->andReturn( '<html><title>Admin</title></html>' );

		Functions\expect( 'admin_url' )
			->once()
			->andReturn( 'https://example.com/wp-admin/' );

		Functions\expect( '__' )
			->with( 'Url is an admin page.', 'rocket' )
			->andReturn( 'Url is an admin page.' );

		$payload = $this->callMethod( 'https://example.com/wp-admin/' );

		$this->assertTrue( $payload['error'] );
		$this->assertSame( 'Url is an admin page.', $payload['message'] );
	}
}
