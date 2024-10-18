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
		if ( $this->is_not_valid_taxonomies_page() ) {
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
		return $this->is_not_valid_taxonomies_page() ? '' : $html;
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
	 * Check if we are on any taxonomy frontend page, but the url query is not valid.
	 *
	 * @return bool (True when not valid page, False if it's a valid taxonomy page)
	 */
	private function is_not_valid_taxonomies_page() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return false;
		}
		$taxonomies_query_vars = wpm_apply_filters_typed( 'string[]', 'rocket_cache_taxonomy_query_vars', $this->get_all_taxonomies_query_var() );

		foreach ( $taxonomies_query_vars as $taxonomy_query_var ) {
			if ( $this->is_not_valid_taxonomy_page( $taxonomy_query_var ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if we are on the taxonomy frontend page, but it's not valid url query.
	 *
	 * @param string $taxonomy_query_var Current taxonomy query var to check.
	 * @return bool (True when not valid taxonomy page, False if it's a valid one)
	 */
	private function is_not_valid_taxonomy_page( $taxonomy_query_var ) {
		global $wp_query;
		return isset( $wp_query->query_vars[ $taxonomy_query_var ], $wp_query->query[ $taxonomy_query_var ] )
			&&
			$wp_query->query_vars[ $taxonomy_query_var ] !== $wp_query->query[ $taxonomy_query_var ];
	}
}
