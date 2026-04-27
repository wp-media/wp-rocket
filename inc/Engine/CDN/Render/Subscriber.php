<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Render;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the CDN Render module.
 *
 * Wires the Controller methods to the appropriate WordPress hooks
 * for CDN driver sections, tabs, status indicator, upsell banner,
 * and exclusion fields.
 *
 * @since 3.22
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @param Controller $controller Controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_cdn_driver_sections'     => [
				[ 'add_rocketcdn_paid_section' ],
				[ 'add_rocketcdn_free_section' ],
				[ 'add_exclude_cdn_section' ],
				[ 'add_purge_cdn_cache_section' ],
			],
			'rocket_cdn_built_in_page_list'  => 'render_built_in_page_list',
			'rocket_cdn_built_in_page_rows'  => 'render_built_in_page_rows',
			'rocket_cdn_driver_tabs'         => 'render_cdn_driver_tabs',
			'rocket_after_built_in_cdn_list' => 'render_upsell_banner',
			'rocket_cdn_settings_fields'     => 'add_exclusions_fields',
		];
	}

	/**
	 * Adds the RocketCDN Paid section to the CDN driver sections.
	 *
	 * @since 3.22
	 *
	 * @param array $sections CDN driver sections.
	 *
	 * @return array
	 */
	public function add_rocketcdn_paid_section( array $sections ): array {
		return $this->controller->add_rocketcdn_paid_section( $sections );
	}

	/**
	 * Adds the RocketCDN Free section to the CDN driver sections.
	 *
	 * @since 3.22
	 *
	 * @param array $sections CDN driver sections.
	 *
	 * @return array
	 */
	public function add_rocketcdn_free_section( array $sections ): array {
		return $this->controller->add_rocketcdn_free_section( $sections );
	}

	/**
	 * Adds the Exclude CDN section to the CDN driver sections.
	 *
	 * @since 3.22
	 *
	 * @param array $sections CDN driver sections.
	 *
	 * @return array
	 */
	public function add_exclude_cdn_section( array $sections ): array {
		return $this->controller->add_exclude_cdn_section( $sections );
	}

	/**
	 * Adds the Purge CDN Cache section to the CDN driver sections.
	 *
	 * @since 3.22
	 *
	 * @param array $sections CDN driver sections.
	 *
	 * @return array
	 */
	public function add_purge_cdn_cache_section( array $sections ): array {
		return $this->controller->add_purge_cdn_cache_section( $sections );
	}

	/**
	 * Renders the built-in CDN page list table.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_built_in_page_list(): void {
		$this->controller->render_built_in_page_list();
	}

	/**
	 * Renders the built-in CDN page list rows.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_built_in_page_rows(): void {
		$this->controller->render_built_in_page_rows();
	}

	/**
	 * Renders the CDN driver tabs.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_cdn_driver_tabs(): void {
		$this->controller->render_cdn_driver_tabs();
	}

	/**
	 * Renders the upsell banner for RocketCDN upgrade.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_upsell_banner(): void {
		$this->controller->render_upsell_banner();
	}

	/**
	 * Adds exclusion fields for CDN to the settings fields array.
	 *
	 * @since 3.22
	 *
	 * @param array $fields Existing settings fields array.
	 *
	 * @return array
	 */
	public function add_exclusions_fields( array $fields ): array {
		return $this->controller->add_exclusions_fields( $fields );
	}
}
