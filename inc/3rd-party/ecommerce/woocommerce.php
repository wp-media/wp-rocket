<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WooCommerce' ) ) :

    add_filter( 'rocket_cache_query_strings', '__rocket_cache_v_query_string' );
    add_filter( 'update_option_woocommerce_default_customer_address', '__rocket_after_update_single_options', 10, 2 );
    add_action( 'woocommerce_save_product_variation', 'rocket_clean_cache_after_woocommerce_save_product_variation', 10 );

endif;

/**
 * Clean product cache on variation update
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param int $variation_id ID of the variation
 */
function rocket_clean_cache_after_woocommerce_save_product_variation( $variation_id ) {
    if ( $product_id = wp_get_post_parent_id( $variation_id ) ) {
        rocket_clean_post( $product_id );
    }
}

/**
 * Automatically cache v query string when WooCommerce geolocation with cache compatibility option is active
 *
 * @since 2.8.6
 * @author Rémy Perona
 *
 * @param array $query_strings list of query strings to cache
 * @return array Updated list of query strings to cache
 */
function __rocket_cache_v_query_string( $query_strings ) {
    if ( 'geolocation_ajax' === get_option( 'woocommerce_default_customer_address' ) ) {
        $query_strings[] = 'v';
    }

    return $query_strings;
}

add_action( 'activate_woocommerce/woocommerce.php', '__rocket_activate_woocommerce', 11 );
function __rocket_activate_woocommerce() {
    add_filter( 'rocket_cache_query_strings', '__rocket_cache_v_query_string' );

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_woocommerce/woocommerce.php', '__rocket_deactivate_woocommerce', 11 );
function __rocket_deactivate_woocommerce() {
    remove_filter( 'rocket_cache_query_strings', '__rocket_cache_v_query_string' );

    // Regenerate the config file
    rocket_generate_config_file();
}

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