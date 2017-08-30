<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( class_exists( 'VarnishPurger' ) ) :
	add_action( 'admin_init', 'rocket_clear_cache_after_varnish_http_purge' );
	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Varnish HTTP Purge plugin
	 *
	 * @since 2.5.5
	 *
	 * @return void
	 */
	function rocket_clear_cache_after_varnish_http_purge() {
		if ( isset( $_GET['vhp_flush_all'] ) && current_user_can( 'manage_options' ) && check_admin_referer( 'varnish-http-purge' ) ) {
			// Clear all caching files.
			rocket_clean_domain();

			// Preload cache.
			run_rocket_preload_cache( 'cache-preload' );
		}
	}
endif;

/*
 @since 2.5.5
 * For not conflit with Varnish HTTP Purge
*/
add_action( 'after_rocket_clean_domain', 'rocket_clean_varnish_http_purge' );
/**
 * Call the cache server to purge the cache with Varnish HTTP Purge.
 *
 * @since 2.5.5
 *
 * @return void
 */
function rocket_clean_varnish_http_purge() {
	if ( class_exists( 'VarnishPurger' ) ) {
		$url    = home_url( '/?vhp-regex' );
		$p      = wp_parse_url( $url );
		$path   = '';
		$pregex = '.*';

		// Build a varniship.
		if ( defined( 'VHP_VARNISH_IP' ) && VHP_VARNISH_IP ) {
			$varniship = VHP_VARNISH_IP;
		} else {
			$varniship = get_option( 'vhp_varnish_ip' );
		}

		if ( isset( $p['path'] ) ) {
			$path = $p['path'];
		}

		$schema = apply_filters( 'varnish_http_purge_schema', 'http://' );

		// If we made varniship, let it sail.
		if ( ! empty( $varniship ) ) {
			$purgeme = $schema . $varniship . $path . $pregex;
		} else {
			$purgeme = $schema . $p['host'] . $path . $pregex;
		}

		wp_remote_request(
			$purgeme,
			array(
				'method'   => 'PURGE',
				'blocking' => false,
				'headers'  => array(
					'host'           => $p['host'],
					'X-Purge-Method' => 'regex',
				),
			)
		);

		do_action( 'after_purge_url', $url, $purgeme );
	}
}
