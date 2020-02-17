<?php
namespace WP_Rocket\Preload;

use WP_Background_Process;

/**
 * Abstract class to be extended by preload process classes.
 * Extends the background process class for the preload background process.
 * The class extending this one must have a $action property and a task() method at least.
 *
 * @since  3.6
 * @author Grégory Viguier
 *
 * @see WP_Background_Process
 */
abstract class Process extends WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @since  3.2
	 * @var    string
	 * @author Remy Perona
	 */
	protected $prefix = 'rocket';

	/**
	 * Format the item to an array.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return array The formatted item. An empty array for invalid items.
	 */
	public function format_item( $item ) {
		if ( is_string( $item ) ) {
			$item = [
				'url' => $item,
			];
		} elseif ( ! is_array( $item ) ) {
			return [];
		}

		if ( empty( $item['url'] ) ) {
			return [];
		}

		$item['mobile'] = ! empty( $item['mobile'] );

		return $item;
	}

	/**
	 * Tell if mobile preload is enabled.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_mobile_preload_enabled() {
		$enabled = get_rocket_option( 'manual_preload' ) && get_rocket_option( 'cache_mobile' ) && get_rocket_option( 'do_caching_mobile_files' );

		/**
		 * Tell if mobile preload is enabled.
		 *
		 * @since  3.6
		 * @author Grégory Viguier
		 *
		 * @param bool   $enabled True when enabled. False otherwise.
		 * @param string $action  Specific action identifier for the current preload type.
		 */
		return (bool) apply_filters( 'rocket_mobile_preload_enabled', $enabled, $this->action );
	}

	/**
	 * Get the user agent to use for the item.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param  array $item {
	 *     The item to preload: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return string
	 */
	public function get_item_user_agent( array $item ) {
		if ( $item['mobile'] ) {
			return 'WP Rocket/Preload iPhone';
		}

		return 'WP Rocket/Preload';
	}

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return bool False.
	 */
	protected function maybe_preload( $item ) {
		$item = $this->format_item( $item );

		if ( ! $item || $this->is_already_cached( $item ) ) {
			return false;
		}

		$this->preload( $item );

		usleep( absint( get_rocket_option( 'sitemap_preload_url_crawl', 500000 ) ) );

		return false;
	}

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param  array $item {
	 *     The item to preload: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 */
	protected function preload( array $item ) {
		/**
		 * Filters the arguments for the partial preload request.
		 *
		 * @since  2.10.8 'rocket_preload_url_request_args'
		 * @since  3.2 'rocket_partial_preload_url_request_args'
		 * @since  3.6 "rocket_{$this->action}_url_request_args"
		 * @author Remy Perona
		 *
		 * @param array $args Request arguments.
		 */
		$args = apply_filters(
			"rocket_{$this->action}_url_request_args",
			[
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => $this->get_item_user_agent( $item ),
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		wp_remote_get( esc_url_raw( $item['url'] ), $args );
	}

	/**
	 * Check if the cache file for $item already exists.
	 *
	 * @since  3.2
	 * @since  3.6 $item is an array.
	 * @author Remy Perona
	 *
	 * @param  array $item {
	 *     The item to preload: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return bool
	 */
	protected function is_already_cached( $item ) {
		static $https;

		if ( ! isset( $https ) ) {
			$https = is_ssl() && get_rocket_option( 'cache_ssl' ) ? '-https' : '';
		}

		$url = get_rocket_parse_url( $item['url'] );

		/** This filter is documented in inc/functions/htaccess.php */
		if ( apply_filters( 'rocket_url_no_dots', false ) ) {
			$url['host'] = str_replace( '.', '_', $url['host'] );
		}

		$url['path'] = trailingslashit( $url['path'] );

		if ( '' !== $url['query'] ) {
			$url['query'] = '#' . $url['query'] . '/';
		}

		$mobile          = $item['mobile'] ? '-mobile' : '';
		$file_cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . $url['host'] . strtolower( $url['path'] . $url['query'] ) . 'index' . $mobile . $https . '.html';

		return rocket_direct_filesystem()->exists( $file_cache_path );
	}
}
