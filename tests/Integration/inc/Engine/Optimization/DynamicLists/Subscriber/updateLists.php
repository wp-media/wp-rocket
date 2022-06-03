<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::update_lists
 *
 * @group  DynamicLists
 */
class Test_UpdateLists extends FilesystemTestCase {
	private $api_response;

	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/Subscriber/updateLists.php';

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'api_response' ] );
		delete_transient( 'wpr_dynamic_lists' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $api_response, $expected ) {
		$this->api_response = $api_response;

		add_filter( 'pre_http_request', [ $this, 'api_response' ] );

		do_action( 'rocket_update_dynamic_lists' );

		$this->assertSame(
			$expected['data'],
			$this->filesystem->get_contents( $this->filesystem->getUrl( 'wp-content/wp-rocket-config/dynamic-lists.json' ) )
		);
		$this->assertEquals(
			$expected['transient'],
			get_transient( 'wpr_dynamic_lists' )
		);
	}

	public function api_response() {
		return $this->api_response;
	}
}
