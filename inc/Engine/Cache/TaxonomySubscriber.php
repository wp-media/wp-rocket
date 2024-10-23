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
	 * Get all public taxonomy query vars.
	 *
	 * @return array
	 */
	private function get_all_taxonomies_query_var() {
		$atts = [
			'public'  => true,
			'show_ui' => true,
		];

		$taxonomies = get_taxonomies( $atts, 'objects' );

		if ( empty( $taxonomies ) ) {
			return [];
		}

		$output = [];
		foreach ( $taxonomies as $taxonomy ) {
			$output[] = $taxonomy->query_var;
		}
		return $output;
	}

	/**
	 * Check if we are on the taxonomy frontend page, but it's not valid url query.
	 *
	 * @return bool (True when not valid taxonomy page, False if it's a valid one)
	 */
	private function is_not_valid_taxonomy_page() {
		$term_id = get_queried_object_id();
		if ( empty( $term_id ) ) {
			return false;
		}

		global $wp;

		$term_link    = untrailingslashit( get_term_link( $term_id ) );
		$current_link = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		return $term_link !== $current_link;
	}
}
