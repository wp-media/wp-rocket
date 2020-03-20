<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Mobile;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with AMP
 *
 * @since  3.5.2
 * @author Soponar Cristina
 */
class Amp_Subscriber implements Subscriber_Interface {
	const QUERY       = 'amp';
	const AMP_OPTIONS = 'amp-options';
	/**
	 * Subscribed events for AMP.
	 *
	 * @since  3.5.2
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		$events = [];

		if ( function_exists( 'is_amp_endpoint' ) ) {
			$events['wp']                                    = 'disable_options_on_amp';
			$events['get_rocket_option_cache_query_strings'] = 'is_amp_compatible_callback';
		}

		return $events;
	}

	/**
	 * Add compatibility with AMP query string by adding it as a cached query string.
	 *
	 * @since  3.5.2
	 * @author Soponar Cristina
	 *
	 * @param array $value WP Rocket cache_query_strings value.
	 */
	public function is_amp_compatible_callback( $value ) {
		$options       = get_option( self::AMP_OPTIONS, [] );
		$query_strings = array_diff( $value, [ static::QUERY ] );

		if ( empty( $options['theme_support'] ) ) {
			return $query_strings;
		}

		if ( 'transitional' === $options['theme_support'] ) {
			$query_strings[] = static::QUERY;
		}

		return $query_strings;
	}

	/**
	 * Removes Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP version of a post with the AMP for WordPress plugin from Auttomatic.
	 *
	 * @since  3.5.2
	 * @author Soponar Cristina
	 */
	public function disable_options_on_amp() {
		if ( ! is_amp_endpoint() ) {
			return;
		}

		global $wp_filter;

		remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		add_filter( 'do_rocket_lazyload', '__return_false' );
		unset( $wp_filter['rocket_buffer'] );

		// this filter is documented in inc/front/protocol.php.
		$do_rocket_protocol_rewrite = apply_filters( 'do_rocket_protocol_rewrite', false ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( ( get_rocket_option( 'do_cloudflare', 0 ) && get_rocket_option( 'cloudflare_protocol_rewrite', 0 ) || $do_rocket_protocol_rewrite ) ) {
			remove_filter( 'rocket_buffer', 'rocket_protocol_rewrite', PHP_INT_MAX );
			remove_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );
		}
	}

}
