<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\Rest;

require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Request.php';
require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_REST_Response.php';

use Brain\Monkey\Functions;
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
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::remove_page
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\Rest::remove_page
 * @group  CDN
 * @group  RocketCDN
 */
class Test_RemovePage extends TestCase {

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

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'untrailingslashit' )->alias( function ( $str ) {
			return rtrim( (string) $str, '/' );
		} );
		Functions\when( 'user_trailingslashit' )->alias( function ( $str ) {
			return rtrim( (string) $str, '/' ) . '/';
		} );
		Functions\when( 'rocket_clean_files' )->justReturn( null );

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

	private function build_request( int $id ): \WP_REST_Request {
		$request = new \WP_REST_Request();
		$request->set_param( 'id', $id );

		return $request;
	}

	/**
	 * When the last page is removed, the response must include no_pages=true.
	 */
	public function testShouldReturnNoPagesWhenLastPageRemoved(): void {
		$item      = new \stdClass();
		$item->url = 'http://example.org/about';

		$this->query->method( 'get_item' )->willReturn( $item );
		$this->query->method( 'delete_item' );
		$this->query->method( 'get_total_count' )->willReturn( 0 );
		$this->query->method( 'get_all' )->willReturn( [] );

		$this->render_controller->shouldReceive( 'get_built_in_page_list' )->andReturn( '' );
		$this->render_controller->shouldReceive( 'get_status_indicator_html' )->with( 0 )->andReturn( '' );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )->andReturn( false );

		$this->context->shouldReceive( 'get_free_page_limit' )->andReturn( 3 );

		$response = $this->get_rest()->remove_page( $this->build_request( 1 ) );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['no_pages'] );
	}

	/**
	 * When pages still remain after removal, no_pages must be false.
	 */
	public function testShouldReturnNoPagesAsFalseWhenPagesRemain(): void {
		$remaining = (object) [ 'id' => 2, 'url' => 'http://example.org/blog', 'title' => 'Blog' ];
		$item      = new \stdClass();
		$item->url = 'http://example.org/about';

		$this->query->method( 'get_item' )->willReturn( $item );
		$this->query->method( 'delete_item' );
		$this->query->method( 'get_total_count' )->willReturn( 1 );
		$this->query->method( 'get_all' )->willReturn( [ $remaining ] );

		$this->render_controller->shouldReceive( 'get_built_in_page_list' )->andReturn( '' );
		$this->render_controller->shouldReceive( 'get_status_indicator_html' )->with( 1 )->andReturn( '' );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )->andReturn( false );

		$this->context->shouldReceive( 'get_free_page_limit' )->andReturn( 3 );

		$response = $this->get_rest()->remove_page( $this->build_request( 1 ) );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['no_pages'] );
	}
}
