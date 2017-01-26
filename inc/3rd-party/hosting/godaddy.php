<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WPaaS\Plugin' ) ) :

	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	add_filter( 'set_rocket_wp_cache_define', '__return_true' );

	add_action( 'before_rocket_clean_domain', 'rocket_clean_domain_godaddy' );
	/**
	 * Call the Varnish server to purge the cache with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @return void
	 */
	function rocket_clean_domain_godaddy() {			
		rocket_godaddy_request( 'BAN' );
	}

	add_action( 'before_rocket_clean_file', 'rocket_clean_file_godaddy' );
	/**
	 * Call the Varnish server to purge a specific URL with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @return void
	 */
	function rocket_clean_file_godaddy( $url ) {			
		rocket_godaddy_request( 'PURGE', home_url( $url ) );
	}

	add_action( 'before_rocket_clean_home', 'rocket_clean_home_godaddy', 10, 2 );
	/**
	 * Call the Varnish server to purge the home with GoDaddy.
	 *
	 * @since 2.9.5
	 *
	 * @return void
	 */
	function rocket_clean_home_godaddy( $root, $lang ) {
		$home_url = trailingslashit( get_rocket_i18n_home_url( $lang ) );
		$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );		
		rocket_godaddy_request( 'PURGE', $home_url );
		rocket_godaddy_request( 'PURGE', $home_pagination_url );
	}

	/**
	 * Perform the call to the Varnish server to purge
	 *
	 * @since 2.9.5
	 * @source WPaaS\Cache
	 *
	 * @param string $method can be BAN or PURGE.
	 * @param string $url URL to purge.
	 * @return void
	 */
	function rocket_godaddy_request( $method, $url = null ) {

		$url  = empty( $url ) ? home_url() : $url;
		$host = parse_url( $url, PHP_URL_HOST );
		$url  = set_url_scheme( str_replace( $host, WPaas\Plugin::vip(), $url ), 'http' );

		wp_cache_flush();

		// This forces the APC cache to flush across the server
		update_option( 'gd_system_last_cache_flush', time() );

		wp_remote_request(
			esc_url_raw( $url ),
			[
				'method'   => $method,
				'blocking' => false,
				'headers'  => [
					'Host' => $host,
				],
			]
		);

	}

endif;
