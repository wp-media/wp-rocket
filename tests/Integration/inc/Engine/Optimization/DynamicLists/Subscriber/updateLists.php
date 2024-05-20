<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::update_lists
 *
 * @group  DynamicLists
 */
class Test_UpdateLists extends FilesystemTestCase {
	private $api_response;
	private $original_user;
	private static $user;
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/Subscriber/updateLists.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		delete_transient( 'wpr_dynamic_lists' );
		parent::set_up();

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		remove_filter( 'pre_http_request', [ $this, 'api_response' ] );
		delete_transient( 'wpr_dynamic_lists' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $user, $api_response, $expected ) {
		$this->api_response = $api_response;

		$this->set_reflective_property( $user, 'user', self::$user );

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
