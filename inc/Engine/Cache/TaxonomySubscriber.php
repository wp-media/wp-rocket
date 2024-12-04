<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the taxonomy frontend pages.
 */
class TaxonomySubscriber implements Subscriber_Interface {
	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files' => 'disable_cache_on_not_valid_taxonomy_pages',
			'rocket_buffer'                    => [ 'stop_optimizations_for_not_valid_taxonomy_pages', 1 ],
		];
	}

	/**
	 * Disable caching invalid taxonomy frontend pages.
	 *
	 * @param bool $can_cache Filter callback passed value.
	 * @return bool
	 */
	public function disable_cache_on_not_valid_taxonomy_pages( $can_cache ) {
		if ( $this->is_not_valid_taxonomy_page() ) {
			return false;
		}

		return $can_cache;
	}

	/**
	 * Stop optimizing those invalid taxonomy pages by returning empty html string,
	 * So it fall back to the normal page's HTML.
	 *
	 * @param string $html Page's buffer HTML.
	 * @return string
	 */
	public function stop_optimizations_for_not_valid_taxonomy_pages( $html ) {
		return $this->is_not_valid_taxonomy_page() ? '' : $html;
	}

	/**
	 * Check if we are on the taxonomy frontend page, but it's not valid url query.
	 *
	 * @return bool (True when not valid taxonomy page, False if it's a valid one)
	 */
	private function is_not_valid_taxonomy_page() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return false;
		}

		$term_id = get_queried_object_id();
		if ( empty( $term_id ) ) {
			return false;
		}

		global $wp;

		$term_link = get_term_link( $term_id );
		if ( is_wp_error( $term_link ) ) {
			return false;
		}

		$current_link = home_url( add_query_arg( [], $wp->request ?? '' ) );
		if ( is_paged() ) {
			$term_link = trailingslashit( $term_link ) . 'page/' . get_query_var( 'paged' );
		}

		return urldecode( untrailingslashit( $term_link ) ) !== urldecode( untrailingslashit( $current_link ) );
	}
}
