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
			'rocket_cdn_driver_sections'            => [
				[ 'add_rocketcdn_paid_section' ],
				[ 'add_rocketcdn_free_section' ],
				[ 'add_exclude_cdn_section' ],
				[ 'add_purge_cdn_cache_section' ],
			],
			'rocket_cdn_free_page_list'             => 'render_built_in_page_list',
			'rocket_cdn_free_page_rows'             => 'render_built_in_page_rows',
			'rocket_cdn_driver_tabs'                => 'render_cdn_driver_tabs',
			'rocket_cdn_settings_fields'            => 'add_exclusions_fields',
			'rocket_display_rocketcdn_cta'          => 'maybe_display_rocketcdn_cta',
			'rocket_cdn_tab_badge'                  => 'maybe_hide_cdn_tab_badge',
			'pre_get_rocket_option_cdn'             => 'maybe_pause_cdn_for_inactive_subscription',
			'pre_get_rocket_option_rocketcdn_state' => 'get_rocketcdn_state',
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

	/**
	 * Displays the RocketCDN Call to Action on the CDN tab of WP Rocket settings page.
	 *
	 * @param bool $display Whether to display the CTA. Default true.
	 *
	 * @since 3.22
	 *
	 * @return bool
	 */
	public function maybe_display_rocketcdn_cta( bool $display = true ): bool {
		return $this->controller->maybe_display_rocketcdn_cta( $display );
	}

	/**
	 * Removes the "NEW" badge from the Content Delivery tab for existing paid subscribers.
	 *
	 * @since 3.22
	 *
	 * @param string $badge Current badge label.
	 *
	 * @return string
	 */
	public function maybe_hide_cdn_tab_badge( string $badge ): string {
		return $this->controller->maybe_hide_cdn_tab_badge( $badge );
	}

	/**
	 * Pauses the CDN for inactive subscriptions.
	 *
	 * @since 3.22
	 *
	 * @param mixed $cdn CDN Option.
	 *
	 * @return mixed
	 */
	public function maybe_pause_cdn_for_inactive_subscription( $cdn ) {
		return $this->controller->maybe_pause_cdn_for_inactive_subscription( $cdn );
	}

	/**
	 * Returns the current RocketCDN state for hidden field sync.
	 *
	 * @param mixed $state Current state value.
	 *
	 * @return string
	 */
	public function get_rocketcdn_state( $state ): string {
		return $this->controller->get_rocketcdn_state( $state );
	}
}
