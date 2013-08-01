<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add menu in admin bar
 * From this menu, you can preload the cache files, clear entire domain cache or post cache (front & back-end)
 *
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0
 *
 */

add_action( 'admin_bar_menu', 'rocket_admin_bar', PHP_INT_MAX );
function rocket_admin_bar( $wp_admin_bar )
{
	if( !current_user_can( 'manage_options' ) )
		return;
	$action = 'purge_cache';
	// Parent
    $wp_admin_bar->add_menu(array(
	    'id'    => 'wp-rocket',
	    'title' => 'WP Rocket',
	    'href'  => admin_url( 'options-general.php?page=wprocket' ),
	));

		// Compatibility with WPML
		if( is_rocket_wpml_active() ) {

			// Purge All
			$wp_admin_bar->add_menu(array(
				'parent'	=> 'wp-rocket',
				'id' 		=> 'purge-all',
				'title' 	=> __( 'Vider le cache', 'rocket' ),
				'href' 		=> '#',
			));

            $langlinks = get_rocket_wpml_langs_for_admin_bar();
			foreach( $langlinks as $lang ) {
	            $wp_admin_bar->add_menu( array(
	                'parent' => 'purge-all',
	                'id' 	 =>  'purge-all-' . $lang['code'],
	                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
	                'href'   => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=all&lang='.$lang['code'] ), $action.'_all' ),
	            ));
	        }

		}
		else {

			// Purge All
			$wp_admin_bar->add_menu(array(
				'parent'	=> 'wp-rocket',
				'id' 		=> 'purge-all',
				'title' 	=> __( 'Vider le cache', 'rocket' ),
				'href' 		=> wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=all' ), $action.'_all' ),
			));

		}

		if( is_admin() )
		{
			// Purge a post
			global $pagenow, $post;
			if( $post && $pagenow=='post.php' && isset( $_GET['action'], $_GET['post'] ) )
			{
				$pobject = get_post_type_object( $post->post_type );
				$wp_admin_bar->add_menu(array(
					'parent' => 'wp-rocket',
					'id' 	 => 'purge-post',
					'title'  => sprintf( __( 'Purger cet article', 'rocket' ), $pobject->labels->singular_name ),
					'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=post-'.$post->ID ), $action.'_post-'.$post->ID ),
				));
			}
		}
		else {
			// Purge this URL (frontend)
			$wp_admin_bar->add_menu(array(
				'parent' => 'wp-rocket',
				'id' 	 => 'purge-url',
				'title'  => __( 'Purger cette URL', 'rocket' ),
				'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=url' ), $action.'_url' ),
			));
		}

		$action = 'preload';
        // Go robot gogo !

        // Compatibility with WPML
		if( is_rocket_wpml_active() ) {

			$wp_admin_bar->add_menu(array(
                'parent' => 'wp-rocket',
                'id' 	 => 'preload-cache',
                'title'  => __( 'Précharger le cache', 'rocket' ),
                'href' 	 => '#'
	        ));

            $langlinks = get_rocket_wpml_langs_for_admin_bar();
			foreach( $langlinks as $lang ) {
	            $wp_admin_bar->add_menu( array(
	                'parent' => 'preload-cache',
	                'id' 	 => 'preload-cache-' . $lang['code'],
	                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
	                'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&lang='.$lang['code'] ), $action ),
	            ));
	        }

		}
		else {

			$wp_admin_bar->add_menu(array(
                'parent' => 'wp-rocket',
                'id' 	 => 'preload-cache',
                'title'  => __( 'Précharger le cache', 'rocket' ),
                'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action='.$action ), $action )
	        ));

		}

		// Go to WP Rocket Support
		$wp_admin_bar->add_menu(array(
			'parent' => 'wp-rocket',
			'id' => 'support',
			'title' => __( 'Support', 'rocket' ),
			'href' => 'http://support.wp-rocket.me',
		));
}



/**
 * TO DO - Description
 *
 * @since 1.3.0
 *
 */

function get_rocket_wpml_langs_for_admin_bar() {

	global $sitepress;

	foreach ( $sitepress->get_active_languages() as $lang ) {
		// Get flag
		$flag = $sitepress->get_flag($lang['code']);
        if($flag->from_template){
            $wp_upload_dir = wp_upload_dir();
            $flag_url = $wp_upload_dir['baseurl'] . '/flags/' . $flag->flag;
        }else{
            $flag_url = ICL_PLUGIN_URL . '/res/flags/'.$flag->flag;
        }

		$langlinks[] = array(
            'code'		=> $lang['code'],
            'current'   => $lang['code'] == $sitepress->get_current_language(),
            'anchor'    => $lang['display_name'],
            'flag'      => '<img class="admin_iclflag" src="'.$flag_url.'" alt="'.$lang['code'].'" width="18" height="12" />'
        );
    }

    if( isset( $_GET['lang'] ) && $_GET['lang'] == 'all' ) {
        array_unshift( $langlinks, array(
            'code'		=> 'all',
            'current'   => 'all' == $sitepress->get_current_language(),
            'anchor'    => __('All languages', 'sitepress'),
            'flag'      => '<img class="icl_als_iclflag" src="'.ICL_PLUGIN_URL.'/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    }
    else {
        array_push( $langlinks,  array(
            'code'		=> 'all',
            'current'   => 'all' == $sitepress->get_current_language(),
            'anchor'    => __('All languages', 'sitepress'),
            'flag'      => '<img class="icl_als_iclflag" src="'.ICL_PLUGIN_URL.'/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    }

    return $langlinks;
}


/**
 * Add CSS for WP Rocket Admin Bar
 *
 * @since 1.0
 *
 */

add_action( 'wp_before_admin_bar_render', 'rocket_wp_before_admin_bar_render' );
function rocket_wp_before_admin_bar_render()
{ ?>

	<style>
		.count-cache {
			background-color: #464646;
			border-radius: 10px;
			color: white;
			display: inline-block;
			font-size: 11px;
			font-weight: bold;
			height: 13px;
			line-height: 1.2em;
			margin-left: 2px;
			min-width: 12px;
			padding: 2px 3px;
			text-align: center;
		}
		#wpadminbar #wp-admin-bar-support a {
			color: #FF0000;
		}
	</style>
<?php
}