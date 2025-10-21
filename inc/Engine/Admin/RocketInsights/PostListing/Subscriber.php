<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\PostListing;

use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\ContentTrait;

/**
 * Subscriber for enqueuing Rocket Insights assets on post listing pages
 *
 * @since 3.20.1
 */
class Subscriber implements Subscriber_Interface {
	use ContentTrait;

	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Constructor.
	 *
	 * @since 3.20.1
	 *
	 * @param Render $render Render instance.
	 */
	public function __construct( Render $render ) {
		$this->render = $render;
	}
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.20.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		$events = [
			'admin_enqueue_scripts' => 'enqueue_post_listing_assets',
		];

		return array_merge( $events, self::get_post_listing_events() );
	}

	/**
	 * Build dynamic events for post listing pages based on public post types.
	 *
	 * @since 3.20.1
	 *
	 * @return array<string, string|array> Associative array of hook => callback(s).
	 */
	private static function get_post_listing_events(): array {
		$events     = [];
		$post_types = self::get_public_post_type_slugs();
		foreach ( $post_types as $post_type ) {
			$events[ "manage_{$post_type}_posts_columns" ]       = 'add_rocket_insights_column';
			$events[ "manage_{$post_type}_posts_custom_column" ] = [ 'render_rocket_insights_column', 10, 2 ];
		}
		return $events;
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

		$post_type_slugs = wp_list_pluck( $this->get_public_post_types(), 'post_type' );
		return in_array( $screen->post_type, $post_type_slugs, true );
	}

	/**
	 * Adds the Rocket Insights column to the post listing table.
	 *
	 * @since 3.20.1
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array Modified columns array with Rocket Insights column.
	 */
	public function add_rocket_insights_column( array $columns ): array {
		$columns['rocket_insights'] = __( 'Rocket Insights', 'rocket' );

		return $columns;
	}

	/**
	 * Renders the content for the Rocket Insights column.
	 *
	 * @since 3.20.1
	 *
	 * @param string $column  The name of the column.
	 * @param int    $post_id The ID of the current post.
	 *
	 * @return void
	 */
	public function render_rocket_insights_column( string $column, int $post_id ): void {
		if ( 'rocket_insights' !== $column ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( ! $url ) {
			return;
		}

		$this->render->render_rocket_insights_column( $url );
	}
}
