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

	private function is_not_valid_taxonomies_page() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return false;
		}
		$taxonomies_query_vars = wpm_apply_filters_typed( 'array', 'rocket_cache_taxonomy_query_vars', $this->get_all_taxonomies_query_var() );

		foreach ( $taxonomies_query_vars as $taxonomy_query_var ) {
			if ( $this->is_not_valid_taxonomy_page( $taxonomy_query_var ) ) {
				return true;
			}
		}
		return false;
	}

	private function is_not_valid_taxonomy_page( $taxonomy_query_var ) {
		global $wp_query;
		return
			isset( $wp_query->query_vars[ $taxonomy_query_var ], $wp_query->query[ $taxonomy_query_var ] )
			&&
			$wp_query->query_vars[ $taxonomy_query_var ] !== $wp_query->query[ $taxonomy_query_var ];
	}

	public function disable_cache_on_not_valid_taxonomy_pages( $can_cache ) {
		if( $this->is_not_valid_taxonomies_page() ){
			return false;
		}

		return $can_cache;
	}

	public function stop_optimizations_for_not_valid_taxonomy_pages( $html ) {
		return $this->is_not_valid_taxonomies_page() ? '' : $html;
	}


}
