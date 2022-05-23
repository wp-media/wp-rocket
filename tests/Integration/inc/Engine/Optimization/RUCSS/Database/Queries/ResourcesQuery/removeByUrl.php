<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery::create_or_update
 *
 * @group  RUCSS
 */
class Test_RemoveByUrl extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function testShouldRemoveResourceItem() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		$item = [
			'url'     => 'https://www.example.org/style.css',
			'type'    => 'css',
			'content' => '.example{color:red;}',
			'media'   => 'all',
		];

		$added = $rucss_resources_query->add_item( $item );

		// Check that it was added properly for the test.
		$this->assertIsObject( $rucss_resources_query->get_item_by( 'url', $item['url'] ) );

		$rucss_resources_query->remove_by_url( $item['url'] );

		// Check that the method under test has removed it.
		$this->assertFalse( $rucss_resources_query->get_item_by( 'url', $item['url'] ) );
	}
}
