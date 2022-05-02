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
class Test_CreateOrUpdate extends TestCase{
	use DBTrait;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function testShouldCreateNewItemIfNotExists(){
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		$item = [
			'url'           => 'https://www.example.org/style.css',
			'type'          => 'css',
			'content'       => '.example{color:red;}',
			'media'         => 'all',
		];

		$this->assertFalse( $rucss_resources_query->get_item_by( 'url', $item['url'] ) );

		$this->assertGreaterThan( 0, $rucss_resources_query->create_or_update( $item ) );

		$this->assertIsObject( $rucss_resources_query->get_item_by( 'url', $item['url'] ) );
	}

	public function testShouldUpdateItemIfExists(){
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		$item = [
			'url'           => 'https://www.example.org/style.css',
			'type'          => 'css',
			'content'       => '.example{color:red;}',
			'media'         => 'all',
		];

		$original_id = $rucss_resources_query->add_item( $item );

		$this->assertIsObject( $rucss_resources_query->get_item_by( 'url', $item['url'] ) );

		// Change one attribute and assert it's updated.
		$item['content'] = '.content-changed{color:#efefef;}';

		//assert that ID for original item equals to ID returned from the method so we updated it not created.
		$this->assertSame( $original_id, $rucss_resources_query->create_or_update( $item ) );

		$new_row = $rucss_resources_query->get_item_by( 'url', $item['url'] );

		$this->assertIsObject( $new_row );
		$this->assertSame( $new_row->content, $item['content'] );
	}


}
