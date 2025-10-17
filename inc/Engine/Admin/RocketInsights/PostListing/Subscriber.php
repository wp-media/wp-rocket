<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\PostListing;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for enqueuing Rocket Insights assets on post listing pages
 *
 * @since 3.20.1
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.20.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_enqueue_scripts' => 'enqueue_post_listing_assets',
		];
	}

	/**
	 * Enqueues Rocket Insights CSS and JS on post listing pages.
	 *
	 * @since 3.20.1
	 *
	 * @return void
	 */
	public function enqueue_post_listing_assets(): void {
		if ( ! $this->should_enqueue_assets() ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'rocket-insights',
			rocket_get_constant( 'WP_ROCKET_ASSETS_CSS_URL' ) . 'rocket-insights' . $suffix . '.css',
			[],
			rocket_get_constant( 'WP_ROCKET_VERSION' )
		);

		wp_enqueue_script(
			'rocket-insights',
			rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'rocket-insights' . $suffix . '.js',
			[ 'jquery' ],
			rocket_get_constant( 'WP_ROCKET_VERSION' ),
			true
		);
	}

	/**
	 * Determines if assets should be enqueued on the current page.
	 *
	 * @since 3.20.1
	 *
	 * @return bool
	 */
	private function should_enqueue_assets(): bool {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		// Check if we're on a post listing page (edit.php).
		if ( 'edit' !== $screen->base ) {
			return false;
		}

		// Get the list of public post types that WP Rocket caches.
		$cached_post_types = $this->get_cached_post_types();

		// Check if the current post type is in the cached list.
		return in_array( $screen->post_type, $cached_post_types, true );
	}

	/**
	 * Gets the list of public post types that WP Rocket caches.
	 *
	 * @since 3.20.1
	 *
	 * @return array
	 */
	private function get_cached_post_types(): array {
		$post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$post_types[] = 'page';

		/**
		 * Filters the post types excluded from Rocket Insights on post listing pages.
		 *
		 * @since 3.20.1
		 *
		 * @param array $excluded_post_types An array of post type names.
		 *
		 * @return array
		 */
		$excluded_post_types = (array) wpm_apply_filters_typed(
			'array',
			'rocket_insights_excluded_post_types',
			[]
		);

		return array_diff( $post_types, $excluded_post_types );
	}
}
