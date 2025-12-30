<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\PostListing;

use WP_Rocket\Engine\Admin\RocketInsights\{
	Render,
	Context\Context
};
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for enqueuing Rocket Insights assets on post listing pages
 *
 * @since 3.20.1
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Excluded post types list.
	 *
	 * @var string[]
	 */
	private $excluded_post_types = [
		'elementor_library',
		'oceanwp_library',
		'tbuilder_layout',
		'tbuilder_layout_part',
		'slider',
		'karma-slider',
		'tt-gallery',
		'xlwcty_thankyou',
		'fusion_template',
		'blocks',
		'jet-woo-builder',
		'fl-builder-template',
		'cms_block',
		'web-story',
	];

	/**
	 * Constructor.
	 *
	 * @since 3.20.1
	 *
	 * @param Render  $render Render instance.
	 * @param Context $context Context instance.
	 */
	public function __construct( Render $render, Context $context ) {
		$this->render  = $render;
		$this->context = $context;
	}
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.20.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_enqueue_scripts'        => 'enqueue_post_listing_assets',
			'manage_pages_columns'         => 'add_column_to_pages',
			'manage_posts_columns'         => [ 'add_column_to_posts', 10, 2 ],
			'manage_product_posts_columns' => [ 'add_column_to_products', 22 ],
			'manage_pages_custom_column'   => [ 'render_rocket_insights_column', 10, 2 ],
			'manage_posts_custom_column'   => [ 'render_rocket_insights_column', 10, 2 ],
		];
	}

	/**
	 * Add RI column header to pages.
	 *
	 * @param string[] $columns Array of column headers.
	 *
	 * @return array
	 */
	public function add_column_to_pages( $columns ): array {
		if ( $this->is_excluded( 'page' ) ) {
			return $columns;
		}
		return $this->add_rocket_insights_column( $columns );
	}

	/**
	 * Add RI column header to products.
	 *
	 * @param string[] $columns Array of column headers.
	 * @return array
	 */
	public function add_column_to_products( $columns ): array {
		if ( $this->is_excluded( 'product' ) ) {
			return $columns;
		}
		return $this->add_rocket_insights_column( $columns );
	}

	/**
	 * Add RI column header to posts.
	 *
	 * @param string[] $columns Array of column headers.
	 * @param string   $post_type Post type.
	 * @return array
	 */
	public function add_column_to_posts( $columns, $post_type ): array {
		// Don't add for products again, because it's added above.
		if ( 'product' === $post_type ) {
			return $columns;
		}

		if ( $this->is_excluded( $post_type ) ) {
			return $columns;
		}
		return $this->add_rocket_insights_column( $columns );
	}

	/**
	 * Check if post type is excluded or not.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return bool
	 */
	private function is_excluded( $post_type ) {
		$excluded = ! is_post_type_viewable( $post_type ) || in_array( $post_type, $this->excluded_post_types, true );

		/**
		 * Filters the current post type if it should be excluded from Rocket Insights functionality.
		 *
		 * This filter allows developers to prevent the Rocket Insights column from being displayed
		 * on specific post type listing pages. The Rocket Insights column provides performance
		 * testing and scoring for individual posts/pages.
		 *
		 * @param boolean $excluded Exclusion status, defaulted to if post type is not public.
		 * @param string  $post_type Current post type to be tested.
		 *
		 * @example
		 * // Exclude custom post types from Rocket Insights
		 * add_filter( 'rocket_insights_excluded_post_type', function( $excluded, $post_type ) {
		 *     return $excluded || $post_type === 'test';
		 * }, 10, 2 );
		 */
		return (bool) wpm_apply_filters_typed(
			'boolean',
			'rocket_insights_excluded_post_type',
			$excluded,
			$post_type
		);
	}

	/**
	 * Determines if assets should be enqueued on the current page.
	 *
	 * @return bool
	 */
	private function should_enqueue_assets(): bool {
		if ( ! $this->can_display_column() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		// Check if we're on a post listing page (edit.php).
		if ( 'edit' !== $screen->base ) {
			return false;
		}

		return ! $this->is_excluded( $screen->post_type );
	}

	/**
	 * Enqueues Rocket Insights CSS and JS on post listing pages.
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
			[ 'jquery', 'wp-api-fetch', 'wp-polyfill', 'wp-url' ],
			rocket_get_constant( 'WP_ROCKET_VERSION' ),
			true
		);

		wp_localize_script(
			'rocket-insights',
			'rocket_insights_i18n',
			[
				'test_page'           => __( 'Test the page', 'rocket' ),
				'loading_img'         => rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL' ) . 'orange-loading.svg',
				'limit_reached'       => $this->render->get_page_limit_error_message(),
				'url_limit_reached'   => __( 'Maximum number of URLs reached for your license.', 'rocket' ),
				'estimated_time_text' => __( 'Analyzing your page (~1 min).', 'rocket' ),
			]
		);
		wp_localize_script(
			'rocket-insights',
			'rocket_ajax_data',
			[
				'is_free_user' => $this->context->is_free_user(),
			]
		);
	}

	/**
	 * Adds the Rocket Insights column to the post listing table.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array Modified columns array with Rocket Insights column.
	 */
	public function add_rocket_insights_column( array $columns ): array {
		if ( ! $this->can_display_column() ) {
			return $columns;
		}

		// Insert Rocket Insights column before the Date column.
		$new_columns = [];
		$inserted    = false;

		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['rocket_insights'] = __( 'Rocket Insights', 'rocket' );
				$inserted                       = true;
			}
			$new_columns[ $key ] = $value;
		}

		// Fallback: If no date column exists, add at the end.
		if ( ! $inserted ) {
			$new_columns['rocket_insights'] = __( 'Rocket Insights', 'rocket' );
		}

		return $new_columns;
	}

	/**
	 * Renders the content for the Rocket Insights column.
	 *
	 * @param string $column  The name of the column.
	 * @param int    $post_id The ID of the current post.
	 *
	 * @return void
	 */
	public function render_rocket_insights_column( string $column, int $post_id ): void {
		if ( ! $this->can_display_column() ) {
			return;
		}

		if ( 'rocket_insights' !== $column ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( ! $url ) {
			return;
		}

		$this->render->render_rocket_insights_column( $url, $post_id );
	}

	/**
	 * Determines if the Rocket Insights column can be displayed.
	 *
	 * The column is displayed if Rocket Insights is allowed for current context
	 * and if the remote setting is enabled.
	 *
	 * @since 3.20.3
	 *
	 * @return bool True if the column can be displayed, false otherwise.
	 */
	protected function can_display_column(): bool {
		return $this->context->is_allowed() && $this->context->is_remote_setting_enabled();
	}
}
