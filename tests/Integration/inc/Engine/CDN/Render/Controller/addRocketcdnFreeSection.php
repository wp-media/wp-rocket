<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_free_section
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_AddRocketcdnFreeSection extends TestCase {
	use DBTrait;

	/**
	 * @var Controller
	 */
	private $controller;

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

		$container        = apply_filters( 'rocket_container', null );
		$this->controller = $container->get( 'cdn_render_controller' );
		$this->query      = $container->get( 'rocketcdn_query' );

		self::truncateRocketCDNTable();
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		wp_cache_flush();
	}

	public function tear_down() {
		self::truncateRocketCDNTable();
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		for ( $i = 1; $i <= $config['page_count']; $i++ ) {
			$this->query->add_item(
				[
					'url'           => "http://example.org/page-{$i}",
					'title'         => "Page {$i}",
					'modified'      => current_time( 'mysql' ),
					'last_accessed' => current_time( 'mysql' ),
				]
			);
		}

		wp_cache_flush();

		$sections = $this->controller->add_rocketcdn_free_section( [] );

		$this->assertArrayHasKey( 'rocketcdn_free_section', $sections );
		$this->assertSame( $expected, $sections['rocketcdn_free_section']['cta_data']['limit_reached'] );
	}
}
