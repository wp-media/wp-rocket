<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Warmup\APIClient;

use WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient;
use WP_Rocket\Tests\Integration\TestCase;

class Test_SendWarmupRequest extends TestCase {

	private static $api;
	private $mockResponse;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$container = apply_filters( 'rocket_container', null );
		self::$api = $container->get( 'rucss_warmup_api_client' );
	}

	public function tearDown() {
		remove_filter( 'pre_http_request', [ $this, 'injectMockResponse' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $atts, $success, $mockResponse ): void {
		$this->mockResponse = $mockResponse;

		// For how to use this filter to mock a WP_HTTP response
		// @see https://core.trac.wordpress.org/browser/tags/5.7/src/wp-includes/class-http.php#L240
		add_filter( 'pre_http_request', [ $this, 'injectMockResponse' ] );

		if ( $success ) {
			$this->assertTrue( self::$api->send_warmup_request( $atts ) );
		} else {
			$this->assertFalse( self::$api->send_warmup_request( $atts ) );
		}
	}

	public function injectMockResponse( $response ) {
		return $this->mockResponse;
	}
}
