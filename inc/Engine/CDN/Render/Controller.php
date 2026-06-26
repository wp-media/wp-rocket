<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Render;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\License\API\User;

/**
 * Handles business logic for CDN driver sections, exclusion fields,
 * and rendering of CDN-specific UI components (tabs, status indicator, upsell).
 *
 * @since 3.22
 */
class Controller extends Abstract_Render {
	/**
	 * Option name used to store forced pause tracking state.
	 *
	 * @var string
	 */
	private const FORCED_PAUSE_TRACKING_OPTION = 'rocket_rocketcdn_forced_pause_state';

	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * CDN context instance.
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
	 * Page count for RocketCDN Free tier.
	 *
	 * @var int
	 */
	private $page_count = 0;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Cache instance
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param Beacon                 $beacon        Beacon instance.
	 * @param string                 $template_path Path to the view templates.
	 * @param Context                $context       Context instance.
	 * @param Options_Data           $options  Options_Data instance.
	 * @param RocketCDNQuery         $cdn_query RocketCDNQuery instance.
	 * @param SubscriptionController $subscription_controller RocketCDN Subscription controller instance.
	 * @param User                   $user          User instance.
	 * @param Cache                  $cache Cache instance.
	 */
	public function __construct(
		Beacon $beacon,
		string $template_path,
		Context $context,
		Options_Data $options,
		RocketCDNQuery $cdn_query,
		SubscriptionController $subscription_controller,
		User $user,
		Cache $cache
	) {
		parent::__construct( $template_path );

		$this->beacon                  = $beacon;
		$this->context                 = $context;
		$this->options                 = $options;
		$this->cdn_query               = $cdn_query;
		$this->subscription_controller = $subscription_controller;
		$this->user                    = $user;
		$this->cache                   = $cache;
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

		$cdn_beacon = $this->beacon->get_suggest( 'rocketcdn' );

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

		if ( $this->is_cdn_paused() ) {
			$classes[] = 'wpr-cdn-built-in--paused';
		}

		$cdn_beacon = $this->beacon->get_suggest( 'rocketcdn_free' );

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
		$cdn_beacon = $this->beacon->get_suggest( 'purge_cdn' );

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
		}
		$classes = [ 'rocketcdn', 'rocketcdn-shared-section' ];

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
			'class'       => $classes,
		];

		if ( $this->context->is_rocketcdn() && $this->should_disable_element_for_rocketcdn() ) {
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
			'class' => [ 'cdn-shared-section' ],
		];

		if ( $this->context->is_rocketcdn() && $this->should_disable_element_for_rocketcdn() ) {
			$sections['exclude_cdn_section']['class'][] = 'wpr-cdn-disabled';
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
					'rocketcdn-shared-section',
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
			'class'             => [ 'cdn-shared-section', 'wpr-cdn-exclusions' ],
			'sanitize_callback' => 'sanitize_textarea',
		];

		// Disable exclusions fields when subscription is processing.
		foreach ( array_keys( $exclusion_fields ) as $field ) {
			if ( $this->context->is_rocketcdn() && $this->should_disable_element_for_rocketcdn() ) {
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

		if ( $this->user->is_reseller_account() ) {
			return false;
		}

		return true;
	}

	/**
	 * Removes the "NEW" badge from the Content Delivery tab for existing paid RocketCDN subscribers.
	 *
	 * Paid users already know RocketCDN — the badge is only relevant for users
	 * discovering the service for the first time via the free tier.
	 *
	 * @since 3.22
	 *
	 * @param string $badge Current badge label.
	 *
	 * @return string Empty string for paid subscribers, original badge otherwise.
	 */
	public function maybe_hide_cdn_tab_badge( string $badge ): string {
		if ( $this->subscription_controller->is_paid() ) {
			return '';
		}

		return $badge;
	}

	/**
	 * Synchronizes forced pause state and emits tracking events on transitions.
	 *
	 * @param \WP_Screen $screen Current admin screen.
	 *
	 * @return void
	 */
	public function maybe_sync_forced_pause_tracking_state( \WP_Screen $screen ): void {
		if ( 'settings_page_wprocket' !== $screen->id || ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}
		$is_forced  = $this->is_forced_paused();
		$stored     = $this->get_forced_pause_tracking();
		$was_forced = (bool) ( $stored['tracking'] ?? false );

		// Bail out when state hasn't changed to avoid unnecessary option updates and tracking events.
		if ( $is_forced === $was_forced ) {
			return;
		}

		$stored['tracking'] = $is_forced;
		update_option( self::FORCED_PAUSE_TRACKING_OPTION, $stored, false );

		// Clear whole cache.
		$this->cache->clear_all_cache();

		/**
		 * Fires when the CDN state changes between paused and active.
		 *
		 * @param string $new_state The new state of the CDN: 'paused' or 'active'.
		 * @param string $reason    'wpr_forced_pause' when pausing, 'wpr_forced_resume' when resuming.
		 * @since 3.22
		 */
		do_action(
			'rocket_rocketcdn_cdn_state_changed',
			$is_forced ? 'paused' : 'active',
			$is_forced ? 'wpr_forced_pause' : 'wpr_forced_resume'
		);
	}

	/**
	 * Renders the CDN driver tabs.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_cdn_driver_tabs(): void {

		$driver = $this->context->get_driver();
		$data   = [
			'disable_other_cdn' => Context::ROCKETCDN_PAID_TYPE === $driver,
			'cdn_type'          => $this->options->get( 'cdn_type', Context::ROCKETCDN_TYPE ),
			'display_tabs'      => ! $this->is_cdn_type_filtered(),
			'rocketcdn_mode'    => Context::ROCKETCDN_PAID_TYPE === $driver ? 'RocketCDN Paid' : 'RocketCDN Free',
		];

		echo $this->generate( 'partials/cdn/cdn-driver-tabs', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the expired WP Rocket license notice.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function render_expired_wpr_licence_notice(): void {
		if ( ! $this->should_display_licence_expired_notice() ) {
			return;
		}

		$data = [
			'renewal_url' => $this->user->get_renewal_url(),
		];

		echo $this->generate( 'partials/cdn/wpr-licence-expired-notice', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Filter the CDN option to pause CDN for users with inactive subscriptions.
	 *
	 * If the user has an inactive subscription, this will force the CDN option to be false,
	 * effectively pausing CDN functionality until they renew or reactivate their subscription.
	 *
	 * @since 3.22
	 *
	 * @param mixed $cdn Current value of the CDN option.
	 *
	 * @return mixed False if the user has an inactive subscription, original value otherwise.
	 */
	public function maybe_pause_cdn_for_inactive_subscription( $cdn ) {
		// Bail early if not on RocketCDN driver to avoid unnecessary checks.
		if ( ! $this->context->is_rocketcdn() ) {
			return $cdn;
		}

		if ( $this->is_forced_paused() ) {
			$stored = $this->get_forced_pause_tracking();

			// Prevent unnecessary DB write on every request.
			if ( empty( $stored['persistent'] ) ) {
				$stored['persistent'] = true;
				update_option( self::FORCED_PAUSE_TRACKING_OPTION, $stored, false );

				// Clear whole cache.
				$this->cache->clear_all_cache();
			}

			return false;
		}

		return $cdn;
	}

	/**
	 * Determines whether a UI element should be disabled for RocketCDN.
	 *
	 * Returns true only when all of the following are true: the current context
	 * is RocketCDN, the CDN is paused, the user has an active subscription,
	 * the license is invalid, and the subscription is in a loading state.
	 *
	 * @return bool True if the element should be disabled, false otherwise.
	 */
	public function should_disable_element_for_rocketcdn(): bool {
		return $this->is_subscription_loading()
			|| $this->is_cdn_paused()
			|| $this->should_display_licence_expired_notice()
			|| ! $this->subscription_controller->has_active_subscription();
	}

	/**
	 * Filters status indicator texts for the free RocketCDN tier.
	 *
	 * Hooked to {@see 'rocket_rocketcdn_status_indicator_texts'} at priority 10.
	 * Activates the status text once pages have been added, and surfaces an
	 * expiry notice when the WP Rocket licence is invalid.
	 *
	 * @param array $texts                   Text strings for the status indicator.
	 * @param int   $pages_count             Number of pages using RocketCDN.
	 * @param bool  $is_subscription_loading Whether the subscription is loading.
	 * @param bool  $free                    Whether this is for the free version.
	 *
	 * @return array
	 */
	public function get_free_status_indicator_texts( array $texts, int $pages_count, bool $is_subscription_loading, bool $free ): array {
		if ( ! $free ) {
			return $texts;
		}

		if ( $pages_count > 0 ) {
			$texts['status_text'] = $texts['active_status_text'];
			$texts['details']     = __( 'Serving files from 10 edge locations. Covering up to 3 pages.', 'rocket' );
		}

		if ( $this->subscription_controller->is_license_invalid() ) {
			$texts['class']         .= ' wpr-cdn-status--expired';
			$texts['paused_details'] = __( 'RocketCDN is currently paused because your WPRocket licence has expired.', 'rocket' );
		}

		return $texts;
	}

	/**
	 * Filters status indicator texts for the paid RocketCDN tier.
	 *
	 * Hooked to {@see 'rocket_rocketcdn_status_indicator_texts'} at priority 10.
	 * Activates the paid status text and surfaces a cancellation notice during
	 * the grace period.
	 *
	 * @param array $texts                   Text strings for the status indicator.
	 * @param int   $pages_count             Number of pages using RocketCDN.
	 * @param bool  $is_subscription_loading Whether the subscription is loading.
	 * @param bool  $free                    Whether this is for the free version.
	 *
	 * @return array
	 */
	public function get_paid_status_indicator_texts( array $texts, int $pages_count, bool $is_subscription_loading, bool $free ): array {
		if ( $free ) {
			return $texts;
		}

		$texts['details']            = __( 'Serving files from 100+ edge locations', 'rocket' );
		$texts['active_status_text'] = __( 'RocketCDN is active on your website', 'rocket' );
		$texts['status_text']        = $texts['active_status_text'];

		if ( $this->subscription_controller->is_in_grace_period() ) {
			$texts['paused_details'] = __( 'RocketCDN is currently paused because your RocketCDN subscription was cancelled. Please wait up to two days before resuming.', 'rocket' );
			$texts['class']         .= ' wpr-cdn-status--expired';
		}

		return $texts;
	}

	/**
	 * Auto-creates a RocketCDN Free subscription when a previously forced-paused state is resolved.
	 *
	 * @since 3.22.0.2
	 *
	 * @return void
	 */
	public function maybe_auto_create_rocketcdn_free_subscription() {
		// Bail out if customer is outside the grace period to avoid unnecessary subscription creation on new accounts.
		if ( ! $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return;
		}

		// Bail out if the subscription is paid and is still within the cancellation grace period — too early to auto-resume.
		if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_in_grace_period() ) {
			return;
		}

		if ( $this->subscription_controller->is_license_invalid() ) {
			return;
		}

		// Update the forced pause tracking option to indicate the forced pause has been resolved.
		$stored               = $this->get_forced_pause_tracking();
		$stored['persistent'] = false;
		update_option( self::FORCED_PAUSE_TRACKING_OPTION, $stored, false );

		// Bail out if there are no add pages in the free plan — no need to auto create, allow normal flow.
		if ( empty( $this->get_items() ) ) {
			return;
		}

		// Auto-create a new subscription to resume free RocketCDN service.
		$this->subscription_controller->create_subscription();
	}

	/**
	 * Reads the forced pause tracking option, migrating the legacy bool format to the current array format.
	 *
	 * @return array
	 */
	private function get_forced_pause_tracking(): array {
		$stored = get_option( self::FORCED_PAUSE_TRACKING_OPTION, [] );

		// Migrate legacy bool value: the option used to be stored as a plain bool.
		if ( is_bool( $stored ) ) {
			return [ 'tracking' => $stored ];
		}

		return (array) $stored;
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
	 * Checks if the current subscription is active and the license is valid.
	 *
	 * @return bool True when the subscription can be used.
	 */
	private function should_display_licence_expired_notice(): bool {
		return $this->subscription_controller->has_active_subscription() &&
				$this->subscription_controller->is_free() &&
				$this->subscription_controller->is_license_invalid();
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
	private function get_status_indicator_data( int $pages_count, bool $is_subscription_loading, bool $free = true ): array {
		$texts = [
			'paused_status_text' => __( 'RocketCDN is paused', 'rocket' ),
			'active_status_text' => __( 'RocketCDN is active', 'rocket' ),
			'paused_details'     => __( 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.', 'rocket' ),
			'status_text'        => '',
			'details'            => sprintf(
			// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
				__( '%1$sStart with your homepage and add up to 2 more key pages.%2$s Includes unlimited traffic across 10 edge locations.', 'rocket' ),
				'<strong>',
				'</strong>'
			),
			'class'              => '',
		];

		/**
		 * Filters the status indicator text strings for RocketCDN.
		 *
		 * Free- and paid-tier callbacks are registered by default to apply
		 * tier-specific overrides. Additional callbacks may be added to
		 * customise the indicator for custom subscription states.
		 *
		 * @param array $texts                   {
		 *     Text strings and CSS modifier class for the status indicator.
		 *
		 *     @type string $paused_status_text Text displayed when CDN is paused.
		 *     @type string $active_status_text Text displayed when CDN is active.
		 *     @type string $paused_details     Details shown while CDN is paused.
		 *     @type string $status_text        Currently-shown status text (empty = inactive state).
		 *     @type string $details            Currently-shown details text.
		 *     @type string $class              CSS modifier class(es) for the indicator element.
		 * }
		 * @param int   $pages_count             Number of pages currently using RocketCDN.
		 * @param bool  $is_subscription_loading Whether the subscription is currently being processed.
		 * @param bool  $free                    Whether this is for the free version of RocketCDN.
		 *
		 * @since 3.22.0.2
		 */
		$texts = wpm_apply_filters_typed( 'array', 'rocket_rocketcdn_status_indicator_texts', $texts, $pages_count, $is_subscription_loading, $free );

		if ( $is_subscription_loading ) {
			$texts['status_text'] = __( 'Creating your subscription...', 'rocket' );
			$texts['details']     = __( 'Please wait, RocketCDN will be ready and active shortly.', 'rocket' );
		}

		$is_paused = $this->is_cdn_paused();

		if ( $is_paused ) {
			$texts['status_text'] = $texts['paused_status_text'];
			$texts['details']     = $texts['paused_details'];
			$texts['class']      .= ' wpr-cdn-status--paused';
		}

		return [
			'class'                   => $texts['class'],
			'is_active'               => true,
			'status_text'             => $texts['status_text'],
			'details'                 => $texts['details'],
			'active_status_text'      => $texts['active_status_text'],
			'paused_status_text'      => $texts['paused_status_text'],
			'paused_details'          => $texts['paused_details'],
			'is_paused'               => $is_paused,
			'pages_count'             => $pages_count,
			'is_subscription_loading' => $is_subscription_loading,
			'hide_pause_btn'          => ( $is_subscription_loading || 0 === $pages_count ) && ! $is_paused,
		];
	}

	/**
	 * Check if cdn should pause or not.
	 *
	 * @return bool
	 */
	private function is_cdn_paused(): bool {
		return ! (bool) $this->options->get( 'cdn' );
	}

	/**
	 * Determines whether the CDN should be force-paused due to an inactive or invalid subscription state.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the CDN should be force-paused, false otherwise.
	 */
	private function is_forced_paused(): bool {
		// Force paused if paid plan cancelled but in grace period.
		if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_in_grace_period() ) {
			return true;
		}

		if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return true;
		}

		// Force paused if free plan with an invalid WP Rocket licence.
		if ( $this->subscription_controller->is_free() && $this->subscription_controller->is_license_invalid() ) {
			return true;
		}

		// Force paused if subscription cancelled beyond the grace period and WP Rocket licence is invalid.
		if ( $this->subscription_controller->is_cancelled_outside_grace_period() && $this->subscription_controller->is_license_invalid() ) {
			return true;
		}

		return false;
	}
}
