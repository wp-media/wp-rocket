<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\RocketCDNFree;

use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNFree::should_rewrite_url
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {
	use DBTrait;

	/**
	 * @var RocketCDNFree
	 */
	private $driver;

	/**
	 * @var RocketCDNQuery
	 */
	private $query;

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

		$container    = apply_filters( 'rocket_container', null );
		$this->driver = $container->get( 'cdn_driver_free' );
		$this->query  = $container->get( 'rocketcdn_query' );

		self::truncateRocketCDNTable();
		wp_cache_flush();
	}

	public function tear_down() {
		self::truncateRocketCDNTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		if ( $config['is_found'] ) {
			$this->query->add_item(
				[
					'url'           => untrailingslashit( $config['url'] ),
					'title'         => 'Test Page',
					'modified'      => current_time( 'mysql' ),
					'last_accessed' => current_time( 'mysql' ),
				]
			);
			wp_cache_flush();
		}

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
