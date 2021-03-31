<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use WP_Rocket\Tests\Integration\TestCase;

class Test_Treeshake extends TestCase {

	private $options_data = [];

	public function setUp(): void {
		parent::setUp();

		//$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'treeshake', 1 );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_rucss', [ $this, 'set_rucss_option' ] );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [$this, 'set_cached_user'] );
		remove_filter( 'pre_http_request', [$this, 'set_api_response'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $mockApiResponse, $expected ): void {

		var_dump(has_filter('rocket_buffer', 'treeshake'));die();
//		$GLOBALS['wp'] = (object) [
//			'query_vars' => [],
//			'request'    => 'http://example.org/home',
//		];
//
//		$this->donotrocketoptimize = $config['no-optimize'];
//
//		if ( $config['bypass'] ) {
//			$GLOBALS['wp']->query_vars['nowprocket'] = 1;
//		}
//
//		$this->options_data['rucss-enabled'] = $config['rucss-enabled'];
//		add_filter( 'pre_get_rocket_option_rucss', [ $this, 'set_rucss_option' ] );
//
//		if ( $config['logged-in'] ) {
//			$user = $this->factory->user->create();
//			wp_set_current_user( $user );
//		}
//
//		$this->options_data['logged-in-cache'] = $config['logged-in-cache'];
//		add_filter( 'pre_get_rocket_option_cache_logged_user', [$this, 'set_cached_user'] );
//
//		$this->options_data['api-response'] = $mockApiResponse;
//		add_filter( 'pre_http_request', [$this, 'set_api_response'] );
//
		$this->assertEquals( $expected, apply_filters( 'rocket_buffer', $config['html'] ) );
	}

	public function set_rucss_option() {
		return $this->options_data['rucss-enabled'];
	}

	public function set_cached_user() {
		return $this->options_data['logged-in-cache'];
	}
}
