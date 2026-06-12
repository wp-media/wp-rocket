<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as BaseTestCase;

/**
 * @group RocketCDN
 * @group AdminOnly
 */
class Test_RocketCDNCta extends BaseTestCase {
	use DBTrait;

	/**
	 * Controller instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\Render\Controller
	 */
	private $controller;

	/**
	 * RocketCDN query instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN
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

		$container         = apply_filters( 'rocket_container', null );
		$this->controller  = $container->get( 'cdn_render_controller' );
		$this->query       = $container->get( 'rocketcdn_query' );

		self::truncateRocketCDNTable();
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
	}

	public function tear_down() {
		self::truncateRocketCDNTable();
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );

		parent::tear_down();
	}

	public function testShouldExposeHiddenCollapsedAndExpandedStates() {
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$cta_data = $sections['rocketcdn_free_section']['cta_data'];

		$this->assertFalse( $cta_data['is_visible'] );
		$this->assertFalse( $cta_data['is_expanded'] );

		$this->add_page( 'http://example.org/page-1', 'Page 1' );
		wp_cache_flush();
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$cta_data = $sections['rocketcdn_free_section']['cta_data'];

		$this->assertTrue( $cta_data['is_visible'] );
		$this->assertFalse( $cta_data['is_expanded'] );

		$this->add_page( 'http://example.org/page-2', 'Page 2' );
		$this->add_page( 'http://example.org/page-3', 'Page 3' );
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$cta_data = $sections['rocketcdn_free_section']['cta_data'];

		$this->assertTrue( $cta_data['is_visible'] );
		$this->assertTrue( $cta_data['is_expanded'] );
	}

	/**
	 * Adds a free-tier page to the RocketCDN table.
	 *
	 * @param string $url Page URL.
	 * @param string $title Page title.
	 * @return void
	 */
	private function add_page( string $url, string $title ): void {
		$this->query->add_item(
			[
				'url'           => $url,
				'title'         => $title,
				'modified'      => current_time( 'mysql' ),
				'last_accessed' => current_time( 'mysql' ),
			]
		);
	}
}
