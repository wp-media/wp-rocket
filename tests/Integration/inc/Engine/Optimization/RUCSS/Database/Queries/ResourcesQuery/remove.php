<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery::create_or_update
 *
 * @group  RUCSS
 */
class Test_Remove extends TestCase {
	use DBTrait;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function testShouldReturnFalseWhenItemNotExists() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		$this->assertFalse( $rucss_resources_query->remove('https://www.example.org/style.css') );
	}

	public function testShouldRemoveItemIfExists() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		$item = [
			'url'     => 'https://www.example.org/style.css',
			'type'    => 'css',
			'content' => '.example{color:red;}',
			'media'   => 'all',
		];

		$id = $rucss_resources_query->add_item( $item );

		$this->assertEquals( $id, $rucss_resources_query->remove($item['url'] ) );
	}
}
