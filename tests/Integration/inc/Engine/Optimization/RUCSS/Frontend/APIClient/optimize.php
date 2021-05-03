<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use WP_Rocket\Tests\Integration\TestCase;

class Test_Optmize extends TestCase {

	private static $api;
	private $mockResponse;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$container = apply_filters( 'rocket_container', null );
		self::$api = $container->get( 'rucss_frontend_api_client' );
	}

	public function tearDown() {
		remove_filter( 'pre_http_request', [ $this, 'injectMockResponse' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $mockResponse, $expected ): void {
		$this->mockResponse = $mockResponse;

		// For how to use this filter to mock a WP_HTTP response
		// @see https://core.trac.wordpress.org/browser/tags/5.7/src/wp-includes/class-http.php#L240
		add_filter( 'pre_http_request', [ $this, 'injectMockResponse' ] );

		$this->assertSame(
			$expected,
			self::$api->optimize( $config['html'], $config['url'], $config['options'] ) );
	}

	public function injectMockResponse( $response ) {
		return $this->mockResponse;
	}
}
