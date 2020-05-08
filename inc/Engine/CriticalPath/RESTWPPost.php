<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Error;

/**
 * Class RESTWPPost
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
class RESTWPPost extends RESTWP {

	/**
	 * Part of route namespace for this inherited class item type.
	 *
	 * @var string $route_namespace to be set with like post, term.
	 */
	protected $route_namespace = 'post';

	/**
	 * Validate the item to be sent to generate CPCSS.
	 *
	 * @since 3.6
	 *
	 * @param int $post_id ID for this post to be validated.
	 * @return true|WP_Error
	 */
	protected function validate_item( $post_id ) {
		$status = get_post_status( $post_id );

		if ( ! $status ) {
			return new WP_Error(
				'post_not_exists',
				__( 'Requested post does not exist.', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		if ( 'publish' !== $status ) {
			return new WP_Error(
				'post_not_published',
				__( 'Cannot generate CPCSS for unpublished post.', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		return true;
	}


	/**
	 * Get url for this item.
	 *
	 * @since 3.6
	 *
	 * @param int $post_id ID for this post to be validated.
	 * @return false|string
	 */
	protected function get_url( $post_id ) {
		return get_permalink( $post_id );
	}

	/**
	 * Get CPCSS file path to save CPCSS code into.
	 *
	 * @since 3.6
	 *
	 * @param int $post_id ID for this post to be validated.
	 * @return string
	 */
	protected function get_path( $post_id ) {
		$post_type = get_post_type( $post_id );
		return "{$post_type}-{$post_id}.css";
	}
}
