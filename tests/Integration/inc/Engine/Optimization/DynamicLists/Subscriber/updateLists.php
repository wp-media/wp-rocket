<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Engine\Optimization\DynamicLists\AbstractAPIClient;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::update_lists
 *
 * @group  DynamicLists
 */
class Test_UpdateLists extends FilesystemTestCase {
	use HttpRequestTrait;

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

		$this->setup_http();

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		delete_transient( 'wpr_dynamic_lists' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $user, $api_response, $expected ) {
		$this->set_reflective_property( $user, 'user', self::$user );

		// Each list provider requests its own endpoint; they all get the same response.
		$this->config['http'] = [
			AbstractAPIClient::API_URL . 'exclusions/list'           => $api_response,
			AbstractAPIClient::API_URL . 'delay-js-exclusions/list'  => $api_response,
			AbstractAPIClient::API_URL . 'incompatible-plugins/list' => $api_response,
		];

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
}
