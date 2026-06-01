<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Render;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;

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
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Options_Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * RocketCDNQuery instance.
	 *
	 * @var RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * RocketCDN Subscription controller instance.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * User client instance.
	 *
	 * @var UserClient
	 */
	private $user_client;

	/**
	 * Page count for RocketCDN Free tier.
	 *
	 * @var int
	 */
	private $page_count = 0;

	/**
	 * Constructor.
	 *
	 * @param Beacon                 $beacon        Beacon instance.
	 * @param string                 $template_path Path to the view templates.
	 * @param Context                $context       Context instance.
	 * @param Options_Data           $options  Options_Data instance.
	 * @param RocketCDNQuery         $cdn_query RocketCDNQuery instance.
	 * @param SubscriptionController $subscription_controller RocketCDN Subscription controller instance.
	 * @param UserClient             $user_client User client instance.
	 */
	public function __construct(
		Beacon $beacon,
		string $template_path,
		Context $context,
		Options_Data $options,
		RocketCDNQuery $cdn_query,
		SubscriptionController $subscription_controller,
		UserClient $user_client
	) {
		parent::__construct( $template_path );

		$this->beacon                  = $beacon;
		$this->context                 = $context;
		$this->options                 = $options;
		$this->cdn_query               = $cdn_query;
		$this->subscription_controller = $subscription_controller;
		$this->user_client             = $user_client;
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
		if ( Context::ROCKETCDN_PAID_TYPE !== $this->context->get_driver() ) {
			return $sections;
		}

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$status_indicator_data           = $this->get_status_indicator_data( 1, $this->is_subscription_loading(), false );
		$status_indicator_data['class'] .= ' wpr-cdn-status-pronounced rocketcdn';

		$sections['rocketcdn_paid_section'] = [
			'title'            => __( 'RocketCDN', 'rocket' ),
			'type'             => 'rocketcdn_paid',
			'class'            => [ 'rocketcdn' ],
			'page'             => 'page_cdn',
			'help'             => [
				'id'  => $cdn_beacon['id'],
				'url' => $cdn_beacon['url'],
			],
			'status_indicator' => $status_indicator_data,
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
		if ( Context::ROCKETCDN_PAID_TYPE === $this->context->get_driver() ) {
			return $sections;
		}

		$classes                 = [ 'rocketcdn' ];
		$is_subscription_loading = $this->is_subscription_loading();
		$this->page_count        = count( $this->get_items() );
		$cta_heading             = sprintf(
					// translators: %1$s = opening strong tag, %2$s = closing strong tag.
					__( '%1$sWant full-site Content Delivery coverage?%2$s Extend RocketCDN to all your pages with unlimited bandwidth.', 'rocket' ),
					'<strong>',
					'</strong>'
				);

		$cta_heading_max_limit = sprintf(
			// translators: %1$s = opening strong tag, %2$s = number of pages allowed, %3$s = closing strong tag.
			__( '%1$sNice work! You’re using RocketCDN on %2$s key pages!%3$s ', 'rocket' ),
			'<strong>',
			$this->context->get_free_page_limit(),
			'</strong>'
		);

		$cta_description = __( 'Upgrade to RocketCDN Pro to extend faster content delivery across all your pages from 100+ edge locations worldwide.', 'rocket' );

		$limit_reached = $this->page_count >= $this->context->get_free_page_limit();

		// Disable input field and buttons when 3 pages are added.
		if ( $limit_reached || $is_subscription_loading ) {
			$classes[] = 'wpr-cdn-built-in--disabled';
		}

		if ( ! (bool) $this->options->get( 'cdn' ) ) {
			$classes[] = 'wpr-cdn-built-in--paused';
		}

		$cdn_beacon = $this->beacon->get_suggest( 'cdn' );

		$sections['rocketcdn_free_section'] = [
			'title'            => __( 'RocketCDN', 'rocket' ),
			'type'             => 'rocketcdn_free',
			'class'            => $classes,
			'page'             => 'page_cdn',
			'help'             => [
				'id'  => $cdn_beacon['id'],
				'url' => $cdn_beacon['url'],
			],
			'status_indicator' => $this->get_status_indicator_data( $this->page_count, $is_subscription_loading ),
			'cta_data'         => [
				'cta_heading'           => $cta_heading,
				'cta_heading_max_limit' => $cta_heading_max_limit,
				'cta_description'       => $cta_description,
				'is_visible'            => $this->page_count > 0,
				'is_expanded'           => $limit_reached,
				'limit_reached'         => $limit_reached,
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

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
		}

		$sections['purge_cdn_cache_section'] = [
			// translators: %s is the CDN driver, wrapped in a span for JS targeting.
			'title'       => sprintf( __( 'Purge %s Cache', 'rocket' ), '<span class="rocketcdn-driver-js">RocketCDN</span>' ),
			'type'        => 'purge_cdn_cache_section',
			'description' => sprintf(
				// translators: %s = CDN driver, wrapped in a span for JS targeting.
				__( 'Purges %s cached resources for your website.', 'rocket' ),
				'<span class="rocketcdn-driver-js">RocketCDN</span>'
			),
			'purge_url'   => Utils::get_nonce_post_url( 'rocket_purge_rocketcdn' ),
			'page'        => 'page_cdn',
			'help'        => [
				'id'  => $cdn_beacon['id'],
				'url' => $cdn_beacon['url'],
			],
			'class'       => [ 'rocketcdn' ],
		];

		if ( $this->is_subscription_loading() ) {
			$sections['purge_cdn_cache_section']['class'][] = 'wpr-cdn-disabled';
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
		$exclusion_fields = [];

		if ( Context::ROCKETCDN_PAID_TYPE === $this->context->get_driver() ) {
			$exclusion_fields['cdn_reject_pages'] = [
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

		$exclusion_fields['cdn_reject_files'] = [
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
		foreach ( array_keys( $exclusion_fields ) as $field ) {
			if ( $this->is_subscription_loading() ) {
				$exclusion_fields[ $field ]['class'][]    = 'wpr-cdn-disabled';
				$exclusion_fields[ $field ]['attributes'] = [
					'disabled' => 'disabled',
				];
			}
		}

		return array_merge( $fields, $exclusion_fields );
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
		if ( 0 === $this->page_count ) {
			return;
		}

		echo $this->get_built_in_page_list(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Gets the status indicator HTML for the RocketCDN free section.
	 *
	 * @param int $pages_count            Number of pages currently using RocketCDN.
	 *
	 * @return string The rendered status indicator HTML.
	 */
	public function get_status_indicator_html( int $pages_count ): string {
		$data = $this->get_status_indicator_data( $pages_count, $this->is_subscription_loading() );

		return $this->render_parts_with_data( 'cdn/cdn-status-indicator', $data, true );
	}

	/**
	 * Gets the built-in page list for the CDN settings.
	 *
	 * Generates a partial table list view using the 'rocket_cdn_free_page_rows'
	 * hook to populate the rows of the table.
	 *
	 * @return string The rendered HTML string of the built-in page list table.
	 */
	public function get_built_in_page_list(): string {
		$table_data = [
			'rows_hook' => 'rocket_cdn_free_page_rows',
		];

		return $this->generate( 'partials/table-list', $table_data );
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
	 * Renders the RocketCDN CTA banner.
	 *
	 * @param bool $display Whether to display the CTA. Default true.
	 *
	 * @return bool
	 * @since 3.22
	 */
	public function maybe_display_rocketcdn_cta( bool $display = true ): bool {
		if ( ! $display ) {
			return false;
		}

		if ( $this->is_subscription_loading() ) {
			return false;
		}

		$user = new User( $this->user_client->get_user_data() );

		if ( $user->is_reseller_account() ) {
			return false;
		}

		return (bool) apply_filters( 'rocket_display_rocketcdn_cta_for_agencies', true, $this->get_page_count() );
	}

	/**
	 * Gets the current number of RocketCDN free-tier pages.
	 *
	 * @return int
	 */
	private function get_page_count(): int {
		if ( 0 !== $this->page_count ) {
			return $this->page_count;
		}

		return (int) $this->cdn_query->get_total_count( false );
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
			'disable_other_cdn' => Context::ROCKETCDN_PAID_TYPE === $this->context->get_driver(),
			'cdn_type'          => $this->options->get( 'cdn_type', Context::ROCKETCDN_TYPE ),
			'display_tabs'      => ! $this->is_cdn_type_filtered(),
		];

		echo $this->generate( 'partials/cdn/cdn-driver-tabs', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
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
		$disable_class = $this->is_subscription_loading() ? 'wpr-cdn-disabled' : '';
		$delete_button = '<button type="button" class="wpr-table-list__delete ' . esc_attr( $disable_class ) . '" data-id="' . esc_attr( $page->id ) . '" aria-label="' . esc_attr__( 'Remove page', 'rocket' ) . '">'
			. '<span class="wpr-icon-trash"></span>'
			. '</button>';

		return [
			'columns' => [
				[
					'content' => '<a href="' . esc_url( $page->url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $page->title ) . '</a>',
				],
				[
					'type'    => 'actions',
					'content' => $delete_button,
				],
			],
		];
	}

	/**
	 * Retrieves the list of pages added to RocketCDN for the free plan.
	 *
	 * @since 3.22
	 *
	 * @return array List of page objects with id, url, and title properties.
	 */
	private function get_items(): array {
		$query_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];

		return $this->cdn_query->query( $query_params );
	}

	/**
	 * Checks if subscription is currently processing.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the subscription is processing, false otherwise.
	 */
	private function is_subscription_loading(): bool {
		return $this->subscription_controller->is_subscription_creation_loading();
	}

	/**
	 * Checks if the CDN type is currently filtered.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the CDN type is filtered, false otherwise.
	 */
	private function is_cdn_type_filtered(): bool {
		$allowed_cdn_types = [ 'rocketcdn', 'byocdn' ];

		/**
		 * Pre-filter cdn_type option.
		 *
		 * @since 3.22
		 *
		 * @param mixed $cdn_type Filtered CDN type.
		*/
		$cdn_type = wpm_apply_filters_typed( 'string|null', 'pre_get_rocket_option_cdn_type', null, '' );

		if ( null !== $cdn_type && in_array( $cdn_type, $allowed_cdn_types, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get status indicator data for RocketCDN.
	 *
	 * Retrieves an array of data used to render the RocketCDN status indicator,
	 * including status text, details, and various flags based on the current
	 * subscription state.
	 *
	 * @param int  $pages_count            Number of pages currently using RocketCDN.
	 * @param bool $is_subscription_loading Whether the subscription is currently being processed.
	 * @param bool $free                    Whether this is for the free version of RocketCDN. Default true.
	 *
	 * @return array {
	 *     Array of status indicator data.
	 *
	 *     @type string $class                  CSS class to apply to the status indicator. Empty string or ' wpr-cdn-status--paused'.
	 *     @type bool   $is_active              Whether RocketCDN is active.
	 *     @type string $status_text            Current status text to display.
	 *     @type string $details                Details text describing the current status.
	 *     @type string $active_status_text     Text to display when RocketCDN is active.
	 *     @type string $paused_status_text     Text to display when RocketCDN is paused.
	 *     @type string $paused_details         Details text to display when RocketCDN is paused.
	 *     @type bool   $is_paused              Whether RocketCDN is currently paused.
	 *     @type int    $pages_count            Number of pages currently using RocketCDN.
	 *     @type bool   $is_subscription_loading Whether the subscription is currently being processed.
	 *     @type bool   $hide_pause_btn         Whether to hide the pause button.
	 * }
	 */
	private function get_status_indicator_data( int $pages_count, bool $is_subscription_loading, bool $free = true ) {
		$paused_status_text = __( 'RocketCDN is paused', 'rocket' );
		$active_status_text = __( 'RocketCDN is active', 'rocket' );
		$paused_details     = __( 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.', 'rocket' );

		$status_text = '';
		$details     = sprintf(
				// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
				__( '%1$sStart with your homepage and add up to 2 more key pages.%2$s Includes unlimited traffic across 10 edge locations.', 'rocket' ),
				'<strong>',
				'</strong>'
			);

		if ( $pages_count > 0 ) {
			$status_text = $active_status_text;
			$details     = __( 'Serving files from 10 edge locations. Covering up to 3 pages.', 'rocket' );
		}

		if ( ! $free ) {
			$details            = __( 'Serving files from 100+ edge locations', 'rocket' );
			$active_status_text = __( 'RocketCDN is active on your website', 'rocket' );
			$status_text        = $active_status_text;
		}

		// Update status inidicator details when subscription is processing.
		if ( $is_subscription_loading ) {
			$status_text = __( 'Creating your subscription...', 'rocket' );
			$details     = __( 'Please wait, RocketCDN will be ready and active shortly.', 'rocket' );
		}

		$is_paused = ! (bool) $this->options->get( 'cdn' );

		if ( $is_paused ) {
			$status_text = $paused_status_text;
			$details     = $paused_details;
		}

		return [
			'class'                   => $is_paused ? ' wpr-cdn-status--paused' : '',
			'is_active'               => true,
			'status_text'             => $status_text,
			'details'                 => $details,
			'active_status_text'      => $active_status_text,
			'paused_status_text'      => $paused_status_text,
			'paused_details'          => $paused_details,
			'is_paused'               => $is_paused,
			'pages_count'             => $pages_count,
			'is_subscription_loading' => $is_subscription_loading,
			'hide_pause_btn'          => ( $is_subscription_loading || 0 === $pages_count ) && ! $is_paused,
		];
	}
}
