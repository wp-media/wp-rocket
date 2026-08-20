<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Rest;

use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Rest::get_status
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class getRecommendations extends RESTfulTestCase {

	protected $config;

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new \ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}

	public function set_up() {
		parent::set_up();

		// Enable Rocket Insights for the test
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Clear any existing recommendations transient
		delete_transient( 'wpr_ri_recommendations' );
	}

	public function tear_down() {
		// Clean up recommendations transient
		delete_transient( 'wpr_ri_recommendations' );

		// Remove Rocket Insights enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		wp_set_current_user( null );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$response = $this->doRestRequest( 'GET', '/wp-rocket/v1/recommendations', [] );

		$this->assertResponse( $response, $expected );
	}

	private function setUpTest( $config ) {
		// Set up user with appropriate capabilities
		if ( isset( $config['user_role'] ) ) {
			if ( 'none' === $config['user_role'] ) {
				wp_set_current_user( 0 );
			} else {
				// Add capability only for administrators
				if ( 'administrator' === $config['user_role'] ) {
					$role = get_role( 'administrator' );
					if ( $role ) {
						$role->add_cap( 'rocket_manage_options' );
					}
				}

				$user = $this->factory()->user->create( [ 'role' => $config['user_role'] ] );
				wp_set_current_user( $user );
			}
		}

		// Set up transient data if provided
		if ( isset( $config['transient_data'] ) ) {
			set_transient( 'wpr_ri_recommendations', $config['transient_data'], DAY_IN_SECONDS );
		}
	}

	private function assertResponse( $response, $expected ) {
		// Check if response is an error
		if ( isset( $expected['is_error'] ) && $expected['is_error'] ) {
			// REST API errors have 'code' and 'message' at top level
			// or they can have 'data' with 'status' inside
			if ( isset( $response['code'] ) ) {
				$this->assertSame( $expected['error_code'], $response['code'] );
			} else {
				$this->fail( 'Expected error response but got: ' . print_r( $response, true ) );
			}
			return;
		}

		// Assert recommendations field exists
		$this->assertArrayHasKey( 'recommendations', $response );

		// Check for error message if in failed status
		if ( isset( $expected['has_error'] ) && $expected['has_error'] ) {
			$this->assertStringContainsString( 'We’re sorry, recommendations are currently unavailable.', $response['recommendations']['html'] );
		}
	}

	protected function doRestRequest( $method, $route, array $body_params = [] ) {
		$request = new \WP_REST_Request( $method, $route );
		$request->set_header( 'Content-Type', 'application/json' );

		if ( ! empty( $body_params ) ) {
			$request->set_body_params( $body_params );
		}

		return rest_do_request( $request )->get_data();
	}
}
