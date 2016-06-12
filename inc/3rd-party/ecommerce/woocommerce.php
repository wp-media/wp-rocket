<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WC_API' ) ) :

    add_filter( 'rocket_cache_reject_uri', '__rocket_exclude_wc_rest_api' );
    function __rocket_exclude_wc_rest_api( $uri ) {
        /**
          * By default, don't cache the WooCommerce REST API.
          *
          * @since 2.6.5
          *
          * @param bool false will force to cache the WooCommerce REST API
         */
        $rocket_cache_reject_wc_rest_api = apply_filters( 'rocket_cache_reject_wc_rest_api', true );
        
        // Exclude WooCommerce REST API
        if ( $rocket_cache_reject_wc_rest_api ) {
            $uri[] = rocket_clean_exclude_file( home_url( '/wc-api/v(.*)' ) );
        }

        return $uri;
    }

endif;