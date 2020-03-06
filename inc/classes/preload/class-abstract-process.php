<?php
namespace WP_Rocket\Preload;

use WP_Background_Process;

/**
 * Abstract class to be extended by preload process classes.
 * Extends the background process class for the preload background process.
 * The class extending this one must have a $action property and a task() method at least.
 *
 * @since  3.5
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
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @param  string       $source An identifier related to the source of the preload.
	 * @return array                The formatted item. An empty array for invalid items.
	 */
	public function format_item( $item, $source = '' ) {
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
		if ( empty( $item['source'] ) ) {
			$item['source'] = is_string( $source ) ? $source : '';
		}

		return $item;
	}

	/**
	 * Tell if mobile preload is enabled.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_mobile_preload_enabled() {
		$enabled = get_rocket_option( 'manual_preload' ) && get_rocket_option( 'cache_mobile' ) && get_rocket_option( 'do_caching_mobile_files' );

		/**
		 * Tell if mobile preload is enabled.
		 *
		 * @since  3.5
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
	 * @since  3.5
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
			return $this->get_mobile_user_agent_prefix() . ' WP Rocket/Preload';
		}

		return 'WP Rocket/Preload';
	}

	/**
	 * Get the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
	 *
	 * @since  3.5.0.2
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_mobile_user_agent_prefix() {
		$prefix = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

		/**
		 * Filter the prefix to prepend to the user agent used for preload to make a HTTP request detected as a mobile device.
		 *
		 * @since  3.5.0.2
		 * @author Grégory Viguier
		 *
		 * @param string $prefix The prefix.
		 */
		$new_prefix = apply_filters( 'rocket_mobile_preload_user_agent_prefix', $prefix );

		if ( empty( $new_prefix ) || ! is_string( $new_prefix ) ) {
			return $prefix;
		}

		return $new_prefix;
	}

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request.
	 *     @type string $source An identifier related to the source of the preload.
	 * }
	 * @return bool True when preload has been launched. False otherwise.
	 */
	protected function maybe_preload( $item ) {
		$item = $this->format_item( $item );

		if ( ! $item || $this->is_already_cached( $item ) ) {
			return false;
		}

		$result = $this->preload( $item );

		usleep( absint( get_rocket_option( 'sitemap_preload_url_crawl', 500000 ) ) );

		return ! is_wp_error( $result );
	}

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @param  array $item {
	 *     The item to preload: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request.
	 *     @type string $source An identifier related to the source of the preload.
	 * }
	 * @return array|WP_Error An array on success. A WP_Error object on failure.
	 */
	private function preload( array $item ) {
		/**
		 * Filters the arguments for the partial preload request.
		 *
		 * @since  2.10.8 'rocket_preload_url_request_args'
		 * @since  3.2 'rocket_partial_preload_url_request_args'
		 * @since  3.5 "rocket_{$this->action}_url_request_args"
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

		return wp_remote_get( esc_url_raw( $item['url'] ), $args );
	}

	/**
	 * Check if the cache file for $item already exists.
	 *
	 * @since  3.2
	 * @since  3.5 $item is an array.
	 * @author Remy Perona
	 *
	 * @param  array $item {
	 *     The item to preload: an array containing the following values.
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request.
	 *     @type string $source An identifier related to the source of the preload.
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

	/**
	 * Stop the process.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 */
	public function cancel_process() {
		if ( method_exists( get_parent_class( $this ), 'cancel_process' ) ) {
			parent::cancel_process();
		}
	}
}
