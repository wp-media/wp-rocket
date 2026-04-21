<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Database\Queries;

use WP_Rocket\Engine\CDN\RocketCDN\Database\Rows\RocketCDN as RocketCDNRow;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Tables\RocketCDN as RocketCDNTable;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN::get_by_url
 *
 * @group RocketCDN
 * @group AdminOnly
 */
class GetByUrlTest extends TestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		self::truncateRocketCDNTable();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$container       = apply_filters( 'rocket_container', null );
		$rocketcdn_query = $container->get( 'rocketcdn_query' );

		foreach ( $config['items'] as $item ) {
			$item_id = $rocketcdn_query->add_item( $item );
			$this->assertNotFalse( $item_id );
		}

		$result = $rocketcdn_query->get_by_url( $config['search_url'] );

		if ( false === $expected['found'] ) {
			$this->assertFalse( $result );
			return;
		}

		$this->assertInstanceOf( RocketCDNRow::class, $result );
		$this->assertSame( $expected['url'], $result->url );
		$this->assertSame( $expected['title'], $result->title );
	}
}
