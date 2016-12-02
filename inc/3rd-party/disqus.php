<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'DISQUS_VERSION' ) ) :
    /**
     * Excludes Disqus scripts from JS minification
     *
     * @since 2.9
     * @author Remy Perona
     *
     * @param Array $excluded_handle An array of JS handles enqueued in WordPress
     * @return Array the updated array of handles
     */
    add_filter( 'rocket_exclude_js', 'rocket_exclude_js_disqus' );
    function rocket_exclude_js_disqus( $excluded_js ) {
        $excluded_js[] = str_replace( home_url(), '', plugins_url( '/disqus-comment-system/media/js/disqus.js' ) );
        $excluded_js[] = str_replace( home_url(), '', plugins_url( '/disqus-comment-system/media/js/count.js' ) );

        return $excluded_js;
    }

endif;