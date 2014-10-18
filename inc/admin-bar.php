<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Add menu in admin bar
 * From this menu, you can preload the cache files, clear entire domain cache or post cache (front & back-end)
 *
 * @since 1.3.5 Compatibility with qTranslate
 * @since 1.3.0 Compatibility with WPML
 * @since 1.0
 */
add_action( 'admin_bar_menu', 'rocket_admin_bar', PHP_INT_MAX );
function rocket_admin_bar( $wp_admin_bar )
{
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) )  {
		return;
	}

	$action = 'purge_cache';
	// Parent
    $wp_admin_bar->add_menu( array(
	    'id'    => 'wp-rocket',
	    'title' => WP_ROCKET_PLUGIN_NAME,
	    'href'  => admin_url( 'options-general.php?page='.WP_ROCKET_PLUGIN_SLUG ),
	));

	// Settings
	$wp_admin_bar->add_menu(array(
		'parent'	=> 'wp-rocket',
		'id' 		=> 'rocket-settings',
		'title' 	=> __( 'Settings', 'rocket' ),
	    'href'  => admin_url( 'options-general.php?page='.WP_ROCKET_PLUGIN_SLUG ),
	));

    if ( rocket_valid_key() ) {

		if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )  {

			// Purge All
			$wp_admin_bar->add_menu(array(
				'parent'	=> 'wp-rocket',
				'id' 		=> 'purge-all',
				'title' 	=> __( 'Clear cache', 'rocket' ),
				'href' 		=> '#',
			));

			if ( $langlinks = get_rocket_wpml_langs_for_admin_bar() ) {

				foreach( $langlinks as $lang ) {
		            $wp_admin_bar->add_menu( array(
		                'parent' => 'purge-all',
		                'id' 	 => 'purge-all-' . $lang['code'],
		                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
		                'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&type=all&lang=' . $lang['code'] ), $action . '_all' ),
		            ));
		        }

			}

		} else if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) || rocket_is_plugin_active( 'polylang/polylang.php' ) ) {

			// Purge All
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-rocket',
				'id' 		=> 'purge-all',
				'title' 	=> __( 'Clear cache', 'rocket' ),
				'href' 		=> '#',
			));

			// Add submenu for each active langs
			if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
				$langlinks = get_rocket_qtranslate_langs_for_admin_bar();
			} else if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
				$langlinks = get_rocket_polylang_langs_for_admin_bar();
			}

			foreach( $langlinks as $lang ) {
	            $wp_admin_bar->add_menu( array(
	                'parent' => 'purge-all',
	                'id' 	 => 'purge-all-' . $lang['code'],
	                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
	                'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&type=all&lang=' . $lang['code'] ), $action . '_all' ),
	            ));
	        }

	        // Add subemnu "All langs"
	        $wp_admin_bar->add_menu( array(
	            'parent' => 'purge-all',
	            'id' 	 => 'purge-all-all',
	            'title'  =>  '<div class="dashicons-before dashicons-admin-site" style="line-height:1.5"> ' . __( 'All languages', 'rocket' ) . '</div>',
	            'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&type=all&lang=all' ), $action . '_all' ),
	        ));

		} else {

			// Purge All
			$wp_admin_bar->add_menu(array(
				'parent'	=> 'wp-rocket',
				'id' 		=> 'purge-all',
				'title' 	=> __( 'Clear cache', 'rocket' ),
				'href' 		=> wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&type=all' ), $action . '_all' ),
			));

		}

		if ( is_admin() ) {

			// Purge a post
			global $pagenow, $post;
			if( $post && 'post.php' == $pagenow && isset( $_GET['action'], $_GET['post'] ) ) {
				$pobject = get_post_type_object( $post->post_type );
				$wp_admin_bar->add_menu(array(
					'parent' => 'wp-rocket',
					'id' 	 => 'purge-post',
					'title'  => __( 'Clear this post', 'rocket' ),
					'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action.'&type=post-' . $post->ID ), $action . '_post-' . $post->ID ),
				));

			}

		} else {

			// Purge this URL (frontend)
			$wp_admin_bar->add_menu( array(
				'parent' => 'wp-rocket',
				'id' 	 => 'purge-url',
				'title'  => __( 'Purge this URL', 'rocket' ),
				'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&type=url' ), $action . '_url' ),
			));

		}

		$action = 'preload';
	    // Go robot gogo !

		if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

			$wp_admin_bar->add_menu( array(
	            'parent' => 'wp-rocket',
	            'id' 	 => 'preload-cache',
	            'title'  => __( 'Preload cache', 'rocket' ),
	            'href' 	 => '#'
	        ));

			if ( $langlinks = get_rocket_wpml_langs_for_admin_bar() ) {
				foreach( $langlinks as $lang ) {
		            $wp_admin_bar->add_menu( array(
		                'parent' => 'preload-cache',
		                'id' 	 => 'preload-cache-' . $lang['code'],
		                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
		                'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&lang=' . $lang['code'] ), $action ),
		            ));
		        }

			}

		} else if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) || rocket_is_plugin_active( 'polylang/polylang.php' )  ) {

			$wp_admin_bar->add_menu( array(
	            'parent' => 'wp-rocket',
	            'id' 	 => 'preload-cache',
	            'title'  => __( 'Preload cache', 'rocket' ),
	            'href' 	 => '#'
	        ));

			if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
				$langlinks = get_rocket_qtranslate_langs_for_admin_bar();
			} else if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
				$langlinks = get_rocket_polylang_langs_for_admin_bar();
			}

			foreach( $langlinks as $lang ) {
	            $wp_admin_bar->add_menu( array(
	                'parent' => 'preload-cache',
	                'id' 	 => 'preload-cache-' . $lang['code'],
	                'title'  => $lang['flag'] . '&nbsp;' . $lang['anchor'],
	                'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&lang=' . $lang['code'] ), $action ),
	            ));
	        }

	        $wp_admin_bar->add_menu( array(
	            'parent' => 'preload-cache',
	            'id' 	 => 'preload-cache-all',
	            'title'  =>  '<div class="dashicons-before dashicons-admin-site" style="line-height:1.5;"> ' . __( 'All languages', 'rocket' ) . '</div>',
	            'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&lang=all' ), $action ),
	        ));

		} else {

			$wp_admin_bar->add_menu( array(
	            'parent' => 'wp-rocket',
	            'id' 	 => 'preload-cache',
	            'title'  => __( 'Preload cache', 'rocket' ),
	            'href' 	 => wp_nonce_url( admin_url( 'admin-post.php?action=' . $action ), $action )
	        ));

		}
	}
	if ( ! rocket_is_white_label() ) {
		// Go to WP Rocket Support
		$wp_admin_bar->add_menu( array(
			'parent' => 'wp-rocket',
			'id'     => 'support',
			'title'  => __( 'Support', 'rocket' ),
			'href'   => 'http://wp-rocket.me/support/',
		));
	}
}

/**
 * Get all langs to display in admin bar for WPML
 *
 * @since 1.3.0
 *
 * @return array $langlinks List of active languages
 */
function get_rocket_wpml_langs_for_admin_bar() {

	global $sitepress;
	$langlinks = array();

	foreach ( $sitepress->get_active_languages() as $lang ) {
		// Get flag
		$flag = $sitepress->get_flag($lang['code']);
        if ( $flag->from_template ) {
            $wp_upload_dir = wp_upload_dir();
            $flag_url = $wp_upload_dir['baseurl'] . '/flags/' . $flag->flag;
        } else {
            $flag_url = ICL_PLUGIN_URL . '/res/flags/' . $flag->flag;
        }

		$langlinks[] = array(
            'code'		=> $lang['code'],
            'current'   => $lang['code'] == $sitepress->get_current_language(),
            'anchor'    => $lang['display_name'],
            'flag'      => '<img class="admin_iclflag" src="' . $flag_url . '" alt="' . $lang['code'] . '" width="18" height="12" />'
        );
    }

    if ( isset( $_GET['lang'] ) && 'all' == $_GET['lang'] ) {
        array_unshift( $langlinks, array(
            'code'		=> 'all',
            'current'   => 'all' == $sitepress->get_current_language(),
            'anchor'    => __( 'All languages', 'sitepress' ),
            'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    } else {
        array_push( $langlinks, array(
            'code'		=> 'all',
            'current'   => 'all' == $sitepress->get_current_language(),
            'anchor'    => __( 'All languages', 'sitepress' ),
            'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    }

    return $langlinks;
}

/**
 * Get all langs to display in admin bar for qTranslate
 *
 * @since 1.3.5
 *
 * @return array $langlinks List of active languages
 */
function get_rocket_qtranslate_langs_for_admin_bar()
{
	global $q_config;
	$langlinks   = array();
	$currentlang = array();

	foreach( $q_config['enabled_languages'] as $lang ) {

		$langlinks[$lang] = array(
            'code'		=> $lang,
            'anchor'    => $q_config['language_name'][$lang],
            'flag'      => '<img src="' . trailingslashit( WP_CONTENT_URL ) . $q_config['flag_location'] . $q_config['flag'][$lang] . '" alt="' . $q_config['language_name'][$lang] . '" width="18" height="12" />'
        );

	}

	if ( isset( $_GET['lang'] ) && qtrans_isEnabled( $_GET['lang'] ) ) {
		$currentlang[ $_GET['lang'] ] = $langlinks[ $_GET['lang'] ];
		unset( $langlinks[ $_GET['lang'] ] );
		$langlinks = $currentlang + $langlinks;
	}

	return $langlinks;
}

/**
 * Get all langs to display in admin bar for Polylang
 *
 * @since 2.2
 *
 * @return array $langlinks List of active languages
 */
function get_rocket_polylang_langs_for_admin_bar()
{
	global $polylang;
	$langlinks   = array();
	$currentlang = array();
	$langs       = $polylang->model->get_languages_list();

	foreach ( $langs as $lang ) {

		$img = empty($lang->flag) ? '' : (false !== strpos($lang->flag, 'img') ? $lang->flag . '&nbsp;' : $lang->flag);

		if( isset( $polylang->curlang->slug ) && $lang->slug == $polylang->curlang->slug ) {
			$currentlang[$lang->slug] = array(
				'code'	 => $lang->slug,
	            'anchor' => $lang->name,
	            'flag'   => $img
			);
		} else {
			$langlinks[$lang->slug] = array(
	            'code'	 => $lang->slug,
	            'anchor' => $lang->name,
	            'flag'   => $img
	        );
		}

	}

	return $currentlang + $langlinks;
}