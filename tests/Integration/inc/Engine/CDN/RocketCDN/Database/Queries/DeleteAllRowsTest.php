<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Database\Queries;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN::delete_all_rows
 *
 * @group RocketCDN
 * @group AdminOnly
 */
class DeleteAllRowsTest extends TestCase {

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

		$initial_count = $rocketcdn_query->query(
			[
				'count' => true,
			],
            false
		);
		$this->assertSame( $expected['initial_count'], $initial_count );

		$result = $rocketcdn_query->delete_all_rows();
		$this->assertIsInt( $result );

		$remaining_count = $rocketcdn_query->query(
			[
				'count' => true,
			],
            false
		);

		$this->assertSame( $expected['remaining_count'], $remaining_count );
	}
}
