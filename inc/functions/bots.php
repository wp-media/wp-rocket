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
function run_rocket_bot( $spider = 'cache-preload', $lang = '' ) {
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
		    if ( ! get_rocket_option( 'manual_preload', true ) ) {
    		    return false;
		    }

			if ( ! $lang ) {
				$urls = get_rocket_i18n_uri();
			} else {
				$urls[] = get_rocket_i18n_home_url( $lang );
			}
		break;
		
		case 'cache-json' :
		    if ( ! get_rocket_option( 'automatic_preload', true ) ) {
    		    return false;
		    }

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
				'timeout'   => 0.01,
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

/**
 * Launches the sitemap preload
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @return void
 */
function run_rocket_sitemap_preload() {
    $jetpack_xml_sitemap = get_rocket_option( 'jetpack_xml_sitemap', false );
    //$google_xml_sitemap  = get_rocket_option( 'google_xml_sitemap', false );
    $yoast_xml_sitemap   = get_rocket_option( 'yoast_xml_sitemap', false );

    if ( ! $sitemaps = get_rocket_option( 'sitemaps', false ) ) {
        if ( ! $jetpack_xml_sitemap 
            //&& ! $google_xml_sitemap
            && ! $yoast_xml_sitemap ) {
            return false;
        }
    }

    if ( $jetpack_xml_sitemap && function_exists( 'jetpack_sitemap_uri' ) ) {
        $sitemaps[] = jetpack_sitemap_uri();
    }

    /*if ( $google_xml_sitemap && class_exists( 'GoogleSitemapGeneratorLoader' ) ) {

    }*/

    if ( $yoast_xml_sitemap && class_exists( 'WPSEO_Sitemaps_Router' ) ) {
        $sitemaps[] = WPSEO_Sitemaps_Router::get_base_url( 'sitemap_index.xml' );
    }

    $sitemaps   = array_unique( $sitemaps );
    $sitemap_id = 0;

    foreach ( $sitemaps as $sitemap_url ) {
        $sitemap_id++;
        $action      = 'rocket_preload_sitemap';
        $_ajax_nonce = wp_create_nonce( 'preload_sitemap-' . $sitemap_id );
        
        rocket_do_async_job( compact( 'action', '_ajax_nonce', 'sitemap_url', 'sitemap_id' ) );
    }
}

/**
 * Processes the sitemaps recursively 
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @return void
 */
function rocket_process_sitemap( $sitemap_url ) {
    $args = array(
        'timeout'    => 0.01,
        'blocking'   => false,
        'user-agent' => 'wprocketbot',
        'sslverify'  => false
    );

    $sitemap = wp_remote_get( esc_url_raw( $sitemap_url ) );

    if ( is_wp_error( $sitemap ) ) {
        continue;
    }

    $xml_data = wp_remote_retrieve_body( $sitemap );

    if ( empty( $xml_data ) ) {
        continue;
    }

    libxml_use_internal_errors();

    $xml = simplexml_load_string( $xml_data );

    if ( false === $xml ) {
        libxml_clear_errors();
        continue;
    }
	
    $url_count = count( $xml->url );
    
    if ( $url_count > 0 ) {
        for ( $i = 0; $i < $url_count; $i++ ) {
        	$page_url = (string) $xml->url[ $i ]->loc;
        	$tmp      = wp_remote_get( esc_url_raw( $page_url ), $args );
        	usleep( get_rocket_option( 'sitemap_preload_url_crawl', '500000' ) );
        }
    } else {
        // Sub sitemap?
        $sitemap_count = count( $xml->sitemap );
        
        if ( $sitemap_count > 0 ) {
        	for ( $i = 0; $i < $sitemap_count; $i++ ) {
        		$sub_sitemap_url = (string) $xml->sitemap[$i]->loc;
        		rocket_process_sitemap( $sub_sitemap_url );
        	}				
        }
    }
}