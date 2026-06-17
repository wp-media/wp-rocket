<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as BaseTestCase;
use WP_Rocket\Engine\Admin\Settings\Render as SettingsRender;

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

	/**
	 * Settings Render instance.
	 *
	 * @var SettingsRender
	 */
	private $settings_render;

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

		$container             = apply_filters( 'rocket_container', null );
		$this->controller      = $container->get( 'cdn_render_controller' );
		$this->query           = $container->get( 'rocketcdn_query' );
		$this->settings_render = $container->get( 'settings_render' );

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

	public function testShouldExposeLimitReachedAsToplevelKeyWhenPageCountEqualsLimit() {
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$this->assertFalse( $sections['rocketcdn_free_section']['limit_reached'], 'limit_reached should be false when no pages are added.' );

		$this->add_page( 'http://example.org/page-1', 'Page 1' );
		$this->add_page( 'http://example.org/page-2', 'Page 2' );
		wp_cache_flush();
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$this->assertFalse( $sections['rocketcdn_free_section']['limit_reached'], 'limit_reached should be false when under the limit.' );

		$this->add_page( 'http://example.org/page-3', 'Page 3' );
		wp_cache_flush();
		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$this->assertTrue( $sections['rocketcdn_free_section']['limit_reached'], 'limit_reached should be true when page count equals the free page limit.' );
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
	 * Tests that the template renders the tooltip wrapper class, disabled attribute, and tooltip text when the page limit is reached.
	 */
	public function testShouldRenderTooltipMarkupWhenLimitReached() {
		$this->add_page( 'http://example.org/page-1', 'Page 1' );
		$this->add_page( 'http://example.org/page-2', 'Page 2' );
		$this->add_page( 'http://example.org/page-3', 'Page 3' );
		wp_cache_flush();

		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$args     = $sections['rocketcdn_free_section'];

		if ( ! empty( $args['class'] ) ) {
			$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
		}

		ob_start();
		$this->settings_render->rocketcdn_free( $args );
		$html = ob_get_clean();

		$this->assertStringContainsString( 'wpr-btn-with-tool-tip', $html, 'Wrapper div should have wpr-btn-with-tool-tip class when limit_reached is true.' );
		$this->assertStringContainsString( 'disabled="disabled"', $html, 'Add Page button should be disabled when limit_reached is true.' );
		$this->assertStringContainsString( 'You have reached the limit of 3 free pages.', $html, 'Tooltip text should appear when limit_reached is true.' );
	}

	/**
	 * Tests that the template does not render the tooltip wrapper class or tooltip text when the page limit is not reached.
	 */
	public function testShouldNotRenderTooltipMarkupWhenLimitNotReached() {
		$this->add_page( 'http://example.org/page-1', 'Page 1' );
		$this->add_page( 'http://example.org/page-2', 'Page 2' );
		wp_cache_flush();

		$sections = $this->controller->add_rocketcdn_free_section( [] );
		$args     = $sections['rocketcdn_free_section'];

		if ( ! empty( $args['class'] ) ) {
			$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
		}

		ob_start();
		$this->settings_render->rocketcdn_free( $args );
		$html = ob_get_clean();

		$this->assertStringNotContainsString( 'wpr-btn-with-tool-tip', $html, 'Wrapper div should not have wpr-btn-with-tool-tip class when limit_reached is false.' );
		$this->assertStringNotContainsString( 'You have reached the limit of 3 free pages.', $html, 'Tooltip text should not appear when limit_reached is false.' );
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
