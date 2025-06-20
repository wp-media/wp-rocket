<?php
namespace WP_Rocket\Engine\Cache\UrlValidation;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the post/page frontend pages.
 */
class PostSubscriber extends AbstractUrlValidation implements Subscriber_Interface {
	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files' => 'disable_cache_on_not_valid_url',
			'rocket_buffer'                    => [ 'stop_optimizations_for_not_valid_url', 1 ],
		];
	}

	/**
	 * Check if we are on the post frontend page, but it's not valid url query.
	 *
	 * @return bool (True when not valid post url, False if it's a valid one)
	 */
	protected function is_not_valid_url(): bool {
		if ( ! is_singular() ) {
			return false;
		}

		$post_id = get_queried_object_id();
		if ( empty( $post_id ) ) {
			return false;
		}

		global $wp;

		$post_link = get_permalink( $post_id );
		if ( ! $post_link ) {
			return false;
		}

		$current_link = home_url( add_query_arg( [], $wp->request ?? '' ) );
		if ( is_paged() ) {
			$post_link = trailingslashit( $post_link ) . 'page/' . get_query_var( 'paged' );
		}

		return urldecode( untrailingslashit( $post_link ) ) !== urldecode( untrailingslashit( $current_link ) );
	}
}
