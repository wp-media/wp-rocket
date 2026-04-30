<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Render;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

/**
 * Handles business logic for CDN driver sections, exclusion fields,
 * and rendering of CDN-specific UI components (tabs, status indicator, upsell).
 *
 * @since 3.22
 */
class Controller extends Abstract_Render {
	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor.
	 *
	 * @param Beacon $beacon        Beacon instance.
	 * @param string $template_path Path to the view templates.
	 */
	public function __construct( Beacon $beacon, string $template_path ) {
		parent::__construct( $template_path );

		$this->beacon = $beacon;
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
		// Todo: Add a check for the RocketCDN subscription status and return early if false.

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['rocketcdn_paid_section'] = [
			'title'                 => __( 'RocketCDN', 'rocket' ),
			'type'                  => 'rocketcdn_paid',
			'class'                 => [ 'rocketcdn' ],
			'page'                  => 'page_cdn',
			'help'                  => [
				'id'  => $cdn_beacon,
				'url' => $cdn_beacon['url'],
			],
			'status_indicator_data' => [
				'is_active'          => true,
				'status_text'        => __( 'RocketCDN is active on your website', 'rocket' ),
				'details'            => __( 'Serving files from 100+ edge locations · Covering 12 pages', 'rocket' ),
				'paused_status_text' => __( 'RocketCDN is paused', 'rocket' ),
				'paused_details'     => __( 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.', 'rocket' ),
				'class'              => 'wpr-cdn-status-pronounced rocketcdn',
			],
		];

		return $sections;
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
		// Todo: Add a check for the RocketCDN subscription status and return early if true.

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['rocketcdn_free_section'] = [
			'title'                 => __( 'RocketCDN', 'rocket' ),
			'type'                  => 'rocketcdn_free',
			'class'                 => [ 'rocketcdn' ],
			'page'                  => 'page_cdn',
			'help'                  => [
				'id'  => $cdn_beacon,
				'url' => $cdn_beacon['url'],
			],
			'status_indicator_data' => [
				'is_active'          => true,
				'status_text'        => __( 'RocketCDN is active', 'rocket' ),
				'details'            => __( 'Serving files from 10 edge locations for free · Covering 2 pages', 'rocket' ),
				'paused_status_text' => __( 'RocketCDN is paused', 'rocket' ),
				'paused_details'     => __( 'RocketCDN is currently paused due to our fair usage policy. Your recent traffic exceeded the expected usage for the free plan. Upgrade to RocketCDN Pro to extend your bandwidth usage.', 'rocket' ),
				'pages_count'       => 0, // RocketCDN Free Tier Todo: Replace with dynamic count of pages added to RocketCDN.
			],
		];

		return $sections;
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
		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['purge_cdn_cache_section'] = [
			// translators: %s is the CDN driver, wrapped in a span for JS targeting.
			'title'       => sprintf( __( 'Purge %s', 'rocket' ), '<span class="rocketcdn-driver-js">RocketCDN</span>' ),
			'type'        => 'purge_cdn_cache_section',
			'description' => sprintf(
				// translators: %s = CDN driver, wrapped in a span for JS targeting.
				__( 'Purges %s cached resources for you website.', 'rocket' ),
				'<span class="rocketcdn-driver-js">RocketCDN</span>'
			),
			'purge_url'   => '#',
			'page'        => 'page_cdn',
			'help'        => [
				'id'  => $cdn_beacon,
				'url' => $cdn_beacon['url'],
			],
		];

		return $sections;
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
		$cdn_exclude_beacon = $this->beacon->get_suggest( 'exclude_cdn' );

		$sections['exclude_cdn_section'] = [
			// translators: %s is the CDN driver, wrapped in a span for JS targeting.
			'title' => sprintf( __( 'Manage %s Exclusions', 'rocket' ), '<span class="rocketcdn-driver-js">RocketCDN</span>' ),
			'type'  => 'nocontainer_with_title',
			'help'  => [
				'id'  => $cdn_exclude_beacon['id'],
				'url' => $cdn_exclude_beacon['url'],
			],
			'page'  => 'page_cdn',
		];

		return $sections;
	}

	/**
	 * Adds exclusion fields for CDN to the settings fields array.
	 *
	 * @since 3.22
	 *
	 * @param array $fields Existing settings fields array.
	 *
	 * @return array Modified fields array with CDN exclusion fields appended.
	 */
	public function add_exclusions_fields( array $fields ): array {
		// Todo: Add a check for the RocketCDN subscription status.
		$fields['cdn_reject_pages'] = [
			'type'              => 'textarea_with_container',
			'label'             => __( 'Exclude Pages from CDN', 'rocket' ),
			'description'       => __( 'Specify URL(s) of pages that should not get served via CDN (one per line).', 'rocket' ),
			'helper'            => __( 'Use (.*) wildcards to exclude all files of a given file type located at a specific path.', 'rocket' ),
			'placeholder'       => '/path/to/page',
			'section'           => 'exclude_cdn_section',
			'page'              => 'page_cdn',
			'default'           => [],
			'class'             => [
				'wpr-cdn-exclusions',
				'rocketcdn',
			],
			'sanitize_callback' => 'sanitize_textarea',
		];

		$fields['cdn_reject_files'] = [
			'type'              => 'textarea_with_container',
			'label'             => __( 'Exclude Files from CDN', 'rocket' ),
			'description'       => __( 'Specify URL(s) of files that should not get served via CDN (one per line).', 'rocket' ),
			'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to exclude all files of a given file type located at a specific path.', 'rocket' ),
			'placeholder'       => '/wp-content/plugins/some-plugins/(.*).css',
			'section'           => 'exclude_cdn_section',
			'page'              => 'page_cdn',
			'default'           => [],
			'class'             => [ 'wpr-cdn-exclusions' ],
			'sanitize_callback' => 'sanitize_textarea',
		];

		return $fields;
	}

	/**
	 * Renders the built-in CDN page list table.
	 *
	 * Builds row data and delegates to the table-list generic partial.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_built_in_page_list(): void {
		$table_data = [
			'rows_hook' => 'rocket_cdn_free_page_rows',
		];

		echo $this->generate( 'partials/table-list', $table_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the built-in CDN page list rows.
	 *
	 * Builds each row and renders them via the table-list-row generic partial.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_built_in_page_rows(): void {
		// Todo: Replace hardcoded data with DB query, e.g. $pages = $this->query->get_pages().
		$pages = [
			(object) [
				'id'    => 1,
				'url'   => '#',
				'title' => __( 'Home page', 'rocket' ),
			],
			(object) [
				'id'    => 2,
				'url'   => '#',
				'title' => __( 'Page 2', 'rocket' ),
			],
		];

		foreach ( $pages as $page ) {
			echo $this->generate( 'partials/table-list-row', $this->build_page_row( $page ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		}
	}

	/**
	 * Builds a table-list-row data array from a page object.
	 *
	 * @since 3.22
	 *
	 * @param object $page Page object with url and title properties.
	 *
	 * @return array Row data for the table-list-row partial.
	 */
	private function build_page_row( object $page ): array {
		$retest_button = '<button type="button" class="wpr-table-list__retest" data-id="' . esc_attr( $page->id ) . '">'
			. '<span class="wpr-icon-bold-refresh"></span>'
			. esc_html__( 'Re-Test Performance', 'rocket' )
			. '</button>';

		$delete_button = '<button type="button" class="wpr-table-list__delete" data-id="' . esc_attr( $page->id ) . '" aria-label="' . esc_attr__( 'Remove page', 'rocket' ) . '">'
			. '<span class="wpr-icon-trash"></span>'
			. '</button>';

		return [
			'columns' => [
				[
					'content' => '<a href="' . esc_url( $page->url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $page->title ) . '</a>',
				],
				[
					'type'    => 'actions',
					'content' => $retest_button . $delete_button,
				],
			],
		];
	}

	/**
	 * Renders the CDN driver tabs.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_cdn_driver_tabs(): void {
		if ( (bool) rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		echo $this->generate( 'partials/cdn/cdn-driver-tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the fair use policy notice when RocketCDN Free is paused due to fair usage limits.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function maybe_display_fair_use_notice(): void {
		$data = [
			'title'       => __( 'Fair Usage Policy', 'rocket' ),
			'description' => __( 'Your RocketCDN Free plan has been paused due to exceeding the fair usage limits. We encourage you to upgrade to RocketCDN Pro for extended bandwidth and additional features.', 'rocket' ),
			'link_url'    => 'https://wp-rocket.me/rocketcdn/',
			'link_text'   => __( 'Upgrade', 'rocket' ),
		];

		echo $this->generate( 'partials/cdn/rocket-cdn/fair-use-notice', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}
}
