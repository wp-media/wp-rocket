<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Launch the Robot
 *
 * @since 2.6.4 Don't preload localhost & .dev domains
 * @since 1.0
 *
 * @param string $spider (default: 'cache-preload') The spider name: cache-preload or cache-json
 * @param string $lang (default: '') The language code to preload
 * @return void
 */
function run_rocket_bot( $spider = 'cache-preload', $lang = '' )
{
	$domain = parse_url( home_url(), PHP_URL_HOST );
	if ( 'localhost' == $domain || pathinfo( $domain, PATHINFO_EXTENSION ) == 'dev' ) {
		return false;
	}

	/**
	 * Filter to manage the bot job
	 *
	 * @since 2.1
	 *
	 * @param bool 	 		 Do the job or not
	 * @param string $spider The spider name
	 * @param string $lang 	 The language code to preload
	*/
	if ( ! apply_filters( 'do_run_rocket_bot', true, $spider, $lang ) ) {
		return false;
	}

	$urls = array();

	switch ( $spider ) {
		case 'cache-preload' :
			if ( ! $lang ) {
				$urls = get_rocket_i18n_uri();
			} else {
				$urls[] = get_rocket_i18n_home_url( $lang );
			}
		break;
		case 'cache-json' :
			$urls[] = WP_ROCKET_URL . 'cache.json';
		break;
		default :
			return false;
		break;
	}

	foreach ( $urls as $start_url ) {
		/**
		 * Fires before WP Rocket Bot is called
		 *
		 * @since 1.1.0
		 *
		 * @param string $spider 	The spider name
		 * @param string $start_url URL that crawl by the bot
		*/
		do_action( 'before_run_rocket_bot', $spider, $start_url );

		wp_remote_get(
			WP_ROCKET_BOT_URL . '?spider=' . $spider . '&start_url=' . $start_url,
			array(
				'timeout'   => 2,
				'blocking'  => false,
				'sslverify' => false,
			)
		);

		/**
		 * Fires after WP Rocket Bot was called
		 *
		 * @since 1.1.0
		 *
		 * @param string $spider 	The spider name
		 * @param string $start_url URL that crawl by the bot
		*/
		do_action( 'after_run_rocket_bot', $spider, $start_url );
	}
}