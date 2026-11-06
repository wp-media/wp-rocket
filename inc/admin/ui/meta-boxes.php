<?php

defined( 'ABSPATH' ) || exit;

/**
 * Add a link "Purge cache" in the post submit area
 *
 * @since 1.0
 */
function rocket_post_submitbox_start() {

	if ( ! rocket_can_display_options() ) {
		return;
	}

	if ( current_user_can( 'rocket_purge_posts' ) ) {
		global $post;

		$cpts = get_post_types(
			[
				'public' => true,
			],
			'objects'
		);

		/**
		 * Filters the post type on submitbox.
		 *
		 * @since 3.12.1
		 *
		 * @param array $cpts Post Types.
		 */
		$cpts = apply_filters( 'rocket_submitbox_options_post_types', $cpts );

		if ( isset( $cpts[ $post->post_type ] ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID );
			printf( '<div id="purge-action"><a class="button-secondary" href="%s">%s</a></div>', esc_url( $url ), esc_html__( 'Clear cache', 'rocket' ) );
		}
	}
}
add_action( 'post_submitbox_start', 'rocket_post_submitbox_start' );
