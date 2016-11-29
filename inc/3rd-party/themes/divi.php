<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

$current_theme = wp_get_theme();

if ( 'Divi' === $current_theme->get( 'Name' ) ) :
    /**
     * Excludes Divi's Salvatorre script from JS minification
     *
     * Exclude it to prevent an error after minification/concatenation
     *
     * @since 2.9
     * @author Remy Perona
     *
     * @param Array $excluded_js An array of JS paths to be excluded
     * @return Array the updated array of paths
     */
    add_filter( 'rocket_exclude_js', 'rocket_exclude_js_divi' );
    function rocket_exclude_js_divi( $excluded_js ) {
        $excluded_js[] = '/wp-content/themes/Divi/includes/builder/scripts/salvattore.min.js';

        return $excluded_js;
    }
endif;