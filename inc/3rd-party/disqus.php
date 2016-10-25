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
    add_filter( 'rocket_excluded_handle_js', '__rocket_exclude_js_disqus' );
    function __rocket_exclude_js_disqus( $excluded_handle ) {
        $excluded_handle[] = 'dsq_embed_script';
        $excluded_handle[] = 'dsq_count_script';
    
        return $excluded_handle;
    }

endif;