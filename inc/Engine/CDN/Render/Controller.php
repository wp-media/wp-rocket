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
		// RFT Todo: Add a check for the RocketCDN subscription status and return early if false.

		if ( 'byocdn' === $this->get_filtered_cdn_type() ) {
			return $sections;
		}

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['rocketcdn_paid_section'] = [
			'title'            => __( 'RocketCDN', 'rocket' ),
			'type'             => 'rocketcdn_paid',
			'class'            => [ 'rocketcdn' ],
			'page'             => 'page_cdn',
			'help'             => [
				'id'  => $cdn_beacon,
				'url' => $cdn_beacon['url'],
			],
			'status_indicator' => [
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
		// RFT Todo: Add a check for the RocketCDN subscription status and return early if true.

		if ( 'byocdn' === $this->get_filtered_cdn_type() ) {
			return $sections;
		}

		$details = sprintf(
				// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
				__( '%1$sStart with your homepage and add up to 2 more key pages.%2$s Includes unlimited traffic across 10 edge locations.', 'rocket' ),
				'<strong>',
				'</strong>'
			);

		$status_text = __( 'RocketCDN is active', 'rocket' );
		$classes     = [ 'rocketcdn' ];

		$is_subscription_loading = $this->is_subscription_loading();

		$pages_count = count( $this->get_items() );

		if ( $pages_count > 0 ) {
			$details = __( 'Serving files from 10 edge locations. Covering up to 3 pages.', 'rocket' );
		}

		// Update status inidicator details when subscription is processing.
		if ( $is_subscription_loading ) {
			$details     = __( 'Please wait, RocketCDN will be ready and active shortly.', 'rocket' );
			$status_text = __( 'Creating your subscription...', 'rocket' );
		}

		// Disable input field and buttons when 3 pages are added.
		if ( $pages_count >= $this->get_limit() || $is_subscription_loading ) {
			$classes[] = 'wpr-cdn-built-in--disabled';
		}

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['rocketcdn_free_section'] = [
			'title'            => __( 'RocketCDN', 'rocket' ),
			'type'             => 'rocketcdn_free',
			'class'            => $classes,
			'page'             => 'page_cdn',
			'help'             => [
				'id'  => $cdn_beacon,
				'url' => $cdn_beacon['url'],
			],
			'status_indicator' => [
				'is_active'               => true,
				'status_text'             => $status_text,
				'details'                 => $details,
				'paused_status_text'      => __( 'RocketCDN is paused', 'rocket' ),
				'paused_details'          => __( 'RocketCDN is currently paused due to our fair usage policy. Your recent traffic exceeded the expected usage for the free plan. Upgrade to RocketCDN Pro to extend your bandwidth usage.', 'rocket' ),
				'pages_count'             => $pages_count,
				'is_subscription_loading' => $is_subscription_loading,
				'hide_pause_btn'          => $is_subscription_loading,
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

		if ( $this->is_subscription_loading() ) {
			$sections['purge_cdn_cache_section']['class'] = [ 'wpr-cdn-disabled' ];
		}

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

		// Disable exclusions fields when subscription is processing.
		if ( $this->is_subscription_loading() ) {
			$sections['exclude_cdn_section']['class'] = [ 'wpr-cdn-disabled' ];
		}

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
		// RFT Todo: Add another check to the condition for the RocketCDN subscription status.
		if ( 'rocketcdn' === $this->get_filtered_cdn_type() ) {
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
		}

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

		// Disable exclusions fields when subscription is processing.
		foreach ( [ 'cdn_reject_pages', 'cdn_reject_files' ] as $field ) {
			if ( $this->is_subscription_loading() ) {
				$fields[ $field ]['class'][]    = 'wpr-cdn-disabled';
				$fields[ $field ]['attributes'] = [
					'disabled' => 'disabled',
				];
			}
		}

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
		$pages = $this->get_items();

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

		$data = [
			// RFT Todo: Replace with dynamic boolean value which results from a check if RocketCDN Paid is active.
			'disable_other_cdn' => false,

			/*
			RFT Todo: Get current CDN driver and pass that for persistent state on page reload.
			Use the WP_Rocket\Admin\Options_Data class to retrieve the cdn_type. Don't use the Context class since that will resolve to the specific CDN driver for RocketCDN.
			*/
			'cdn_type'          => 'rocketcdn',
			'display_tabs'      => ! $this->is_cdn_type_filtered(),
		];

		echo $this->generate( 'partials/cdn/cdn-driver-tabs', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Retrieves the list of pages added to RocketCDN for the free plan.
	 *
	 * @since 3.22
	 *
	 * @return array List of page objects with id, url, and title properties.
	 */
	private function get_items(): array {
		// RFT Todo: Replace hardcoded data with DB query.
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
		return $pages;
	}

	/**
	 * Retrieves the maximum number of pages allowed on the RocketCDN free plan.
	 *
	 * @since 3.22
	 *
	 * @return int Page limit for RocketCDN free plan.
	 */
	private function get_limit(): int {
		// RFT Todo: Replace hardcoded limit with API data.
		return 3;
	}

	/**
	 * Checks if subscription is currently processing.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the subscription is processing, false otherwise.
	 */
	private function is_subscription_loading(): bool {
		// RFT Todo: Implement a check for whether the subscription is currently processing.
		return false;
	}

	/**
	 * Retrieves the filtered CDN type.
	 *
	 * Gets the CDN type from the pre-filter hook with type validation.
	 * Only allows CDN types that are in the allowed list ('rocketcdn', 'byocdn').
	 *
	 * @since 3.22
	 *
	 * @return string|null The filtered CDN type if valid and set, null otherwise.
	 */
	private function get_filtered_cdn_type() {
		$allowed_cdn_types = [ 'rocketcdn', 'byocdn' ];

		/**
		 * Pre-filter cdn_type option.
		 *
		 * @since 3.22
		 *
		 * @param mixed $cdn_type Filtered CDN type.
		*/
		$cdn_type = wpm_apply_filters_typed( 'string|null', 'pre_get_rocket_option_cdn_type', null, '' );

		// RFT Todo: Perform additional checks if a cdn driver is alreaady active and running.

		if ( null !== $cdn_type && ! in_array( $cdn_type, $allowed_cdn_types, true ) ) {
			return null;
		}

		return $cdn_type;
	}

	/**
	 * Checks if the CDN type is currently filtered.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the CDN type is filtered, false otherwise.
	 */
	private function is_cdn_type_filtered(): bool {
		return null !== $this->get_filtered_cdn_type();
	}
}
