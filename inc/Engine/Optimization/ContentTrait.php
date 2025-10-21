<?php

namespace WP_Rocket\Engine\Optimization;

trait ContentTrait {
	/**
	 * Gets all public post types.
	 *
	 * @since 3.9 moved into trait
	 * @since 2.11
	 */
	private static function query_public_post_type_rows(): array {
		global $wpdb;

		$post_types   = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);
		$post_types[] = 'page';

		$excluded_post_types = (array) apply_filters(
			'rocket_cpcss_excluded_post_types',
			[
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
			]
		);

		$post_types = array_values( array_diff( $post_types, $excluded_post_types ) );
		if ( empty( $post_types ) ) {
			return [];
		}

		$esc = esc_sql( $post_types );
		$in  = "'" . implode( "','", $esc ) . "'";

		// Same semantics as before: one row per post_type, only if there is at least one published post.
		$sql = "
			SELECT MAX(ID) AS ID, post_type
			FROM (
				SELECT ID, post_type
				FROM $wpdb->posts
				WHERE post_type IN ($in)
				  AND post_status = 'publish'
				ORDER BY post_date DESC
			) AS posts
			GROUP BY post_type
		";

		return $wpdb->get_results( $sql ) ?: []; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Retrieves all public post types.
	 *
	 * This method returns an array of post types that are publicly accessible.
	 * It is typically used to filter or process content based on post type visibility.
	 *
	 * @return array List of public post types.
	 */
	private function get_public_post_types() {
		return self::query_public_post_type_rows();
	}


	/**
	 * Retrieves the slugs of all public post types.
	 *
	 * @return array An array containing the slugs of public post types.
	 */
	public static function get_public_post_type_slugs(): array {
		$rows = self::query_public_post_type_rows();
		return array_map(
			static function ( $r ) {
				return $r->post_type;
			},
			$rows
			);
	}


	/**
	 * Gets all public taxonomies.
	 *
	 * @since 3.9 moved into trait
	 * @since 2.11
	 */
	private function get_public_taxonomies() {
		global $wpdb;

		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		/**
		 * Filters the taxonomies excluded from critical CSS generation.
		 *
		 * @since  2.11
		 *
		 * @param array $excluded_taxonomies An array of taxonomies names.
		 *
		 * @return array
		 */
		$excluded_taxonomies = (array) apply_filters(
			'rocket_cpcss_excluded_taxonomies',
			[
				'post_format',
				'product_shipping_class',
				'karma-slider-category',
				'truethemes-gallery-category',
				'coupon_campaign',
				'element_category',
				'mediamatic_wpfolder',
				'attachment_category',
			]
		);

		$taxonomies = array_diff( $taxonomies, $excluded_taxonomies );
		$taxonomies = esc_sql( $taxonomies );
		$taxonomies = "'" . implode( "','", $taxonomies ) . "'";

		return $wpdb->get_results(
			"SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( $taxonomies )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy"
		);
	}
}
