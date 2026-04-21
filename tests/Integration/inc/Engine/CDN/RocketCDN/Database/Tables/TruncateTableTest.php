<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Database\Tables;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Database\Tables\RocketCDN::truncate_table
 *
 * @group RocketCDN
 * @group AdminOnly
 */
class TruncateTableTest extends TestCase {
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
		$container = apply_filters( 'rocket_container', null );
		$query     = $container->get( 'rocketcdn_query' );
		$table     = $container->get( 'rocketcdn_table' );

		foreach ( $config['items'] as $item ) {
			$item_id = $query->add_item( $item );
			$this->assertNotFalse( $item_id );
		}

		if ( isset( $expected['table_name_contains'] ) ) {
			$table_name = $table->get_name();
			$this->assertStringContainsString( $expected['table_name_contains'], $table_name );
			return;
		}

		if ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) {
			$initial_count = $query->query(
				[
					'number' => 999,
					'count'  => true,
				],
				false
			);
			$this->assertEquals( count( $config['items'] ), $initial_count );
		}

		$result = $table->truncate_table();

		if ( isset( $expected['result'] ) ) {
			if ( 'boolean' === $expected['result'] ) {
				$this->assertIsBool( $result );
			} else {
				$this->assertEquals( $expected['result'], $result );
			}
		}

		if ( ( ! isset( $config['uninstall_table'] ) || ! $config['uninstall_table'] ) && true === $result ) {
			if ( isset( $expected['remaining_count'] ) ) {
				$remaining_count = $query->query(
					[
						'number' => 999,
						'count'  => true,
					],
					false
				);
				$this->assertEquals( $expected['remaining_count'], $remaining_count );
			}
		}

		if ( isset( $config['uninstall_table'] ) && $config['uninstall_table'] ) {
			$table->install();
		}
	}
}
