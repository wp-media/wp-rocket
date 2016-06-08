<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

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
		$flag = $sitepress->get_flag( $lang['code'] );
        
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
            'anchor'    => __( 'All languages', 'rocket' ),
            'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    } else {
        array_push( $langlinks, array(
            'code'		=> 'all',
            'current'   => 'all' == $sitepress->get_current_language(),
            'anchor'    => __( 'All languages', 'rocket' ),
            'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />'
        ));
    }

    return $langlinks;
}

/**
 * Get all langs to display in admin bar for qTranslate
 *
 * @since 2.7 add fork param
 * @since 1.3.5
 *
 * @param string $fork qTranslate fork name
 * @return array $langlinks List of active languages
 */
function get_rocket_qtranslate_langs_for_admin_bar( $fork = '' ) {
	global $q_config;

	$langlinks   = array();
	$currentlang = array();

	foreach( $q_config['enabled_languages'] as $lang ) {

		$langlinks[ $lang ] = array(
			'code'   => $lang,
			'anchor' => $q_config['language_name'][ $lang ],
			'flag'   => '<img src="' . trailingslashit( WP_CONTENT_URL ) . $q_config['flag_location'] . $q_config['flag'][ $lang ] . '" alt="' . $q_config['language_name'][ $lang ] . '" width="18" height="12" />'
		);

	}

	if ( $fork === 'x' ) {
		if ( isset( $_GET['lang'] ) && qtranxf_isEnabled( $_GET['lang'] ) ) {
			$currentlang[ $_GET['lang'] ] = $langlinks[ $_GET['lang'] ];
			unset( $langlinks[ $_GET['lang'] ] );
			$langlinks = $currentlang + $langlinks;
		}
	} else if ( isset( $_GET['lang'] ) && qtrans_isEnabled( $_GET['lang'] ) ) {
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
function get_rocket_polylang_langs_for_admin_bar() {
	global $polylang;

	$langlinks   = array();
	$currentlang = array();
	$langs       = array();
	$img         = '';

	$pll = function_exists( 'PLL' ) ? PLL() : $polylang;

	if ( isset( $pll ) ) {
    	$langs = $pll->model->get_languages_list();

        if ( ! empty( $langs ) ) {
            foreach ( $langs as $lang ) {
            	if ( ! empty( $lang->flag ) ) {
            		$img = false !== strpos( $lang->flag, 'img' ) ? $lang->flag . '&nbsp;' : $lang->flag;
            	}
        
            	if( isset( $pll->curlang->slug ) && $lang->slug == $pll->curlang->slug ) {
            		$currentlang[ $lang->slug ] = array(
            			'code'   => $lang->slug,
            			'anchor' => $lang->name,
            			'flag'   => $img
            		);
            	} else {
            		$langlinks[ $lang->slug ] = array(
            			'code'   => $lang->slug,
            			'anchor' => $lang->name,
            			'flag'   => $img
            		);
            	}
            }
        }
    }

	return $currentlang + $langlinks;
}

/**
 * Check if a translation plugin is activated
 *
 * @since 2.0
 *
 * @return bool True if a plugin is activated
 */
function rocket_has_i18n() {
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' )  // WPML
		|| rocket_is_plugin_active( 'qtranslate/qtranslate.php' )               // qTranslate
		|| rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' )			    // qTranslate-x
		|| rocket_is_plugin_active( 'polylang/polylang.php' )                   // Polylang
        || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) { 			// Polylang Pro
		return true;
	}

	return false;
}

/**
 * Get infos of all active languages
 *
 * @since 2.0
 *
 * @return array List of language code
 */
function get_rocket_i18n_code() {
	if( ! rocket_has_i18n() ) {
		return false;
	}

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		return array_keys( $GLOBALS['sitepress']->get_active_languages() );
	}

	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) || rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
		return $GLOBALS['q_config']['enabled_languages'];
	}

	if ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
		return pll_languages_list();
	}
}

/**
 * Get all active languages host
 *
 * @since 2.6.8
 *
 * @return array $urls List of all active languages host
 */
function get_rocket_i18n_host() {
	$langs_host = array();

	if ( $langs = get_rocket_i18n_uri() ) {
		foreach ( $langs as $lang ) {
			$langs_host[] = parse_url( $lang, PHP_URL_HOST );
		}
	}

	return $langs_host;
}

/**
 * Get all active languages URI
 *
 * @since 2.0
 *
 * @return array $urls List of all active languages URI
 */
function get_rocket_i18n_uri() {
	$urls = array();
	if ( ! rocket_has_i18n() ) {
		$urls[] = home_url();
		return $urls;
	}

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
			$urls[] = $GLOBALS['sitepress']->language_url( $lang );
		}
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) || rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
    		if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
        		$urls[] = qtrans_convertURL( home_url(), $lang, true );
    		} else if ( rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
        		$urls[] = qtranxf_convertURL( home_url(), $lang, true );
    		}
		}
    } elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
        $pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

        if ( isset( $pll ) ) {
		    $urls = wp_list_pluck( $pll->model->get_languages_list(), 'home_url' );
        }
	}

	return $urls;
}

/**
 * Get directories paths to preserve languages ​​when purging a domain
 * This function is required when the domains of languages (​​other than the default) are managed by subdirectories
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/ and /de/ are deleted.
 * But, if you have a domain for your english and german websites with example.com/en/ and example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 2.0
 *
 * @param string $current_lang The current language code
 * @return array $langs_to_preserve List of directories path to preserve
 */
function get_rocket_i18n_to_preserve( $current_lang ) {
	$langs_to_preserve = array();
	if ( ! rocket_has_i18n() ) {
		return $langs_to_preserve;
	}

	$langs = get_rocket_i18n_code();

	// Unset current lang to the preserve dirs
	$langs = array_flip( $langs );
	if( isset( $langs[$current_lang] ) ) {
		unset( $langs[$current_lang] );
	}
	$langs = array_flip( $langs );

	// Stock all URLs of langs to preserve
	foreach ( $langs as $lang ) {
		list( $host, $path ) = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
		$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $host . '(.*)/' . trim( $path, '/' );
	}

	/**
	 * Filter directories path to preserve of cache purge
	 *
	 * @since 2.1
	 *
	 * @param array $langs_to_preserve List of directories path to preserve
	*/
	$langs_to_preserve = apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );

	return $langs_to_preserve;
}

/**
 * Get all languages subdomains URLs
 *
 * @since 2.1
 *
 * @return array $urls List of languages subdomains URLs
 */
function get_rocket_i18n_subdomains() {
	if ( ! rocket_has_i18n() ) {
		return false;
	}

	$urls = array();
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$option = get_option( 'icl_sitepress_settings' );
		if ( (int) $option['language_negotiation_type'] == 2 ) {
			$urls = get_rocket_i18n_uri();
		}
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		if( (int) $GLOBALS['q_config']['url_mode'] == 3 ) {
			$urls = get_rocket_i18n_uri();
		}
    } elseif ( rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
		if( (int) $GLOBALS['q_config']['url_mode'] == 3 || (int) $GLOBALS['q_config']['url_mode'] == 4 ) {
			$urls = get_rocket_i18n_uri();
		}
	} elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
    	$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

        if ( isset( $pll ) && ( (int) $pll->options['force_lang'] == 2 || (int) $pll->options['force_lang'] == 3 ) ) {
            $urls = get_rocket_i18n_uri();
        }
	}

	return $urls;
}

/**
 * Get home URL of a specific lang
 *
 * @since 2.2
 *
 * @param string $lang (default: '') The language code
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) {
	$url = home_url();
	if ( ! rocket_has_i18n() ) {
		return $url;
	}

	if ( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {
		$url = $GLOBALS['sitepress']->language_url( $lang );
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$url = qtrans_convertURL( home_url(), $lang, true );
    } elseif ( rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
		$url = qtranxf_convertURL( home_url(), $lang, true );
	} elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
    	$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

    	if ( ! empty( $pll->options['force_lang'] ) && isset( $pll->links ) ) {
		    $url = pll_home_url( $lang );
        }
	}

	return $url;
}

/**
 * Get all translated path of a specific post with ID.
 *
 * @since	2.4
 *
 * @param 	int 	$post_id	Post ID
 * @param 	string 	$post_type 	Post Type
 * @param 	string 	$regex 		Regex to include at the end
 * @return 	array	$urls
 */
function get_rocket_i18n_translated_post_urls( $post_id, $post_type = 'page', $regex = null ) {
	$urls  = array();
	$path  = parse_url( get_permalink( $post_id ), PHP_URL_PATH );
	$langs = get_rocket_i18n_code();

	if ( empty( $path ) ) {
		return $urls;
	}

	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		foreach( $langs as $lang ) {
			$urls[] = parse_url( get_permalink( icl_object_id( $post_id, $post_type, true, $lang ) ), PHP_URL_PATH ) . $regex;
		}
	}

	// qTranslate & qTranslate-x
	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) || rocket_is_plugin_active( 'qtranslate-x/qtranslate.php' ) ) {
		$langs  = $GLOBALS['q_config']['enabled_languages'];
		$langs  = array_diff( $langs, array( $GLOBALS['q_config']['default_language'] ) );
		$url    = get_permalink( $post_id );
		$urls[] = parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;

		foreach( $langs as $lang ) {
    		if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
        		$urls[] = parse_url( qtrans_convertURL( $url, $lang, true ), PHP_URL_PATH ) . $regex;
    		} else if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
        		$urls[] = parse_url( qtranxf_convertURL( $url, $lang, true ), PHP_URL_PATH ) . $regex;
    		}
		}
	}

	// Polylang
	if ( rocket_is_plugin_active( 'polylang/polylang.php' ) || rocket_is_plugin_active( 'polylang-pro/polylang.php' ) ) {
    	if ( function_exists( 'PLL' ) && is_object( PLL()->model ) ) {
            $translations = pll_get_post_translations( $post_id );
        } else if ( is_object( $GLOBALS['polylang']->model ) ) {
            $translations = $GLOBALS['polylang']->model->get_translations( 'page', $post_id );
        }

        if ( ! empty( $translations ) ) {
		    foreach ( $translations as $post_id ) {
		    	$urls[] = parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;
		    }
        }
	}

	if ( trim( $path, '/' ) != '' ) {
		$urls[] = $path . $regex;
	}

	$urls = array_unique( $urls );

	return $urls;
}