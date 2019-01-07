<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

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
		// Get flag.
		$flag = $sitepress->get_flag( $lang['code'] );

		if ( $flag->from_template ) {
			$wp_upload_dir = wp_upload_dir();
			$flag_url = $wp_upload_dir['baseurl'] . '/flags/' . $flag->flag;
		} else {
			$flag_url = ICL_PLUGIN_URL . '/res/flags/' . $flag->flag;
		}

		$langlinks[] = array(
			'code'      => $lang['code'],
			'current'   => $lang['code'] === $sitepress->get_current_language(),
			'anchor'    => $lang['display_name'],
			'flag'      => '<img class="icl_als_iclflag" src="' . $flag_url . '" alt="' . $lang['code'] . '" width="18" height="12" />',
		);
	}

	if ( isset( $_GET['lang'] ) && 'all' === $_GET['lang'] ) {
		array_unshift(
			$langlinks, array(
				'code'      => 'all',
				'current'   => 'all' === $sitepress->get_current_language(),
				'anchor'    => __( 'All languages', 'rocket' ),
				'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />',
			)
		);
	} else {
		array_push(
			$langlinks, array(
				'code'      => 'all',
				'current'   => 'all' === $sitepress->get_current_language(),
				'anchor'    => __( 'All languages', 'rocket' ),
				'flag'      => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />',
			)
		);
	}

	return $langlinks;
}

/**
 * Get all langs to display in admin bar for qTranslate
 *
 * @since 2.7 add fork param
 * @since 1.3.5
 *
 * @param string $fork qTranslate fork name.
 * @return array $langlinks List of active languages
 */
function get_rocket_qtranslate_langs_for_admin_bar( $fork = '' ) {
	global $q_config;

	$langlinks   = array();
	$currentlang = array();

	foreach ( $q_config['enabled_languages'] as $lang ) {

		$langlinks[ $lang ] = array(
			'code'   => $lang,
			'anchor' => $q_config['language_name'][ $lang ],
			'flag'   => '<img src="' . trailingslashit( WP_CONTENT_URL ) . $q_config['flag_location'] . $q_config['flag'][ $lang ] . '" alt="' . $q_config['language_name'][ $lang ] . '" width="18" height="12" />',
		);

	}

	if ( 'x' === $fork ) {
		if ( isset( $_GET['lang'] ) && qtranxf_isEnabled( $_GET['lang'] ) ) {
			$currentlang[ $_GET['lang'] ] = $langlinks[ $_GET['lang'] ];
			unset( $langlinks[ $_GET['lang'] ] );
			$langlinks = $currentlang + $langlinks;
		}
	} elseif ( isset( $_GET['lang'] ) && qtrans_isEnabled( $_GET['lang'] ) ) {
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
					$img = strpos( $lang->flag, 'img' ) !== false ? $lang->flag . '&nbsp;' : $lang->flag;
				}

				if ( isset( $pll->curlang->slug ) && $lang->slug === $pll->curlang->slug ) {
					$currentlang[ $lang->slug ] = array(
						'code'   => $lang->slug,
						'anchor' => $lang->name,
						'flag'   => $img,
					);
				} else {
					$langlinks[ $lang->slug ] = array(
						'code'   => $lang->slug,
						'anchor' => $lang->name,
						'flag'   => $img,
					);
				}
			}
		}
	}

	return $currentlang + $langlinks;
}

/**
 * Tell if a translation plugin is activated.
 *
 * @since 2.0
 * @since 3.2.1 Return an identifier on success instead of true.
 *
 * @return string|bool An identifier corresponding to the active plugin. False otherwize.
 */
function rocket_has_i18n() {
	global $sitepress, $q_config, $polylang;

	if ( ! empty( $sitepress ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_active_languages' ) ) {
		// WPML.
		return 'wpml';
	}

	if ( ! empty( $polylang ) && function_exists( 'pll_languages_list' ) ) {
		$languages = pll_languages_list();

		if ( empty( $languages ) ) {
			return false;
		}

		// Polylang, Polylang Pro.
		return 'polylang';
	}

	if ( ! empty( $q_config ) && is_array( $q_config ) ) {
		if ( function_exists( 'qtrans_convertURL' ) ) {
			// qTranslate.
			return 'qtranslate';
		}
		if ( function_exists( 'qtranxf_convertURL' ) ) {
			// qTranslate-x.
			return 'qtranslate-x';
		}
	}

	return false;
}

/**
 * Get infos of all active languages.
 *
 * @since 2.0
 *
 * @return array A list of language codes.
 */
function get_rocket_i18n_code() {
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return false;
	}

	if ( 'wpml' === $i18n_plugin ) {
		// WPML.
		return array_keys( $GLOBALS['sitepress']->get_active_languages() );
	}

	if ( 'qtranslate' === $i18n_plugin || 'qtranslate-x' === $i18n_plugin ) {
		// qTranslate, qTranslate-x.
		return ! empty( $GLOBALS['q_config']['enabled_languages'] ) ? $GLOBALS['q_config']['enabled_languages'] : [];
	}

	if ( 'polylang' === $i18n_plugin ) {
		// Polylang, Polylang Pro.
		return pll_languages_list();
	}

	return false;
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
	$langs      = get_rocket_i18n_uri();

	if ( $langs ) {
		foreach ( $langs as $lang ) {
			$langs_host[] = rocket_extract_url_component( $lang, PHP_URL_HOST );
		}
	}

	return $langs_host;
}

/**
 * Get all active languages URI.
 *
 * @since 2.0
 *
 * @return array $urls List of all active languages URI.
 */
function get_rocket_i18n_uri() {
	$i18n_plugin = rocket_has_i18n();
	$urls        = [];

	if ( 'wpml' === $i18n_plugin ) {
		// WPML.
		foreach ( get_rocket_i18n_code() as $lang ) {
			$urls[] = $GLOBALS['sitepress']->language_url( $lang );
		}
	} elseif ( 'qtranslate' === $i18n_plugin || 'qtranslate-x' === $i18n_plugin ) {
		// qTranslate, qTranslate-x.
		foreach ( get_rocket_i18n_code() as $lang ) {
			if ( 'qtranslate' === $i18n_plugin ) {
				$urls[] = qtrans_convertURL( home_url(), $lang, true );
			} else {
				$urls[] = qtranxf_convertURL( home_url(), $lang, true );
			}
		}
	} elseif ( 'polylang' === $i18n_plugin ) {
		// Polylang, Polylang Pro.
		$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

		if ( ! empty( $pll ) && is_object( $pll ) ) {
			$urls = wp_list_pluck( $pll->model->get_languages_list(), 'search_url' );
		}
	}

	if ( empty( $urls ) ) {
		$urls[] = home_url();
	}

	return $urls;
}

/**
 * Get directories paths to preserve languages ​​when purging a domain.
 * This function is required when the domains of languages (​​other than the default) are managed by subdirectories.
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/ and /de/ are deleted.
 * But, if you have a domain for your english and german websites with example.com/en/ and example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 2.0
 *
 * @param  string $current_lang The current language code.
 * @return array                A list of directories path to preserve.
 */
function get_rocket_i18n_to_preserve( $current_lang ) {
	// Must not be an empty string.
	if ( empty( $current_lang ) ) {
		return [];
	}

	// Must not be anything else but a string.
	if ( ! is_string( $current_lang ) ) {
		return [];
	}

	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return [];
	}

	$langs = get_rocket_i18n_code();

	// Remove current lang to the preserve dirs.
	$langs = array_diff( $langs, [ $current_lang ] );

	// Stock all URLs of langs to preserve.
	$langs_to_preserve = [];

	if ( $langs ) {
		foreach ( $langs as $lang ) {
			$parse_url           = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
			$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $parse_url['host'] . '(.*)/' . trim( $parse_url['path'], '/' );
		}
	}

	/**
	 * Filter directories path to preserve of cache purge.
	 *
	 * @since 2.1
	 *
	 * @param array $langs_to_preserve List of directories path to preserve.
	*/
	return apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
}

/**
 * Get all languages subdomains URLs
 *
 * @since 2.1
 *
 * @return array $urls List of languages subdomains URLs
 */
function get_rocket_i18n_subdomains() {
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return [];
	}

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			$option = get_option( 'icl_sitepress_settings' );

			if ( 2 === (int) $option['language_negotiation_type'] ) {
				return get_rocket_i18n_uri();
			}
			break;
		// qTranslate.
		case 'qtranslate':
			if ( 3 === (int) $GLOBALS['q_config']['url_mode'] ) {
				return get_rocket_i18n_uri();
			}
			break;
		// qTranslate-x.
		case 'qtranslate-x':
			if ( 3 === (int) $GLOBALS['q_config']['url_mode'] || 4 === (int) $GLOBALS['q_config']['url_mode'] ) {
				return get_rocket_i18n_uri();
			}
			break;
		// Polylang, Polylang Pro.
		case 'polylang':
			$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

			if ( ! empty( $pll ) && is_object( $pll ) && ( 2 === (int) $pll->options['force_lang'] || 3 === (int) $pll->options['force_lang'] ) ) {
				return get_rocket_i18n_uri();
			}
	}

	return [];
}

/**
 * Get home URL of a specific lang.
 *
 * @since 2.2
 *
 * @param  string $lang The language code. Default is an empty string.
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) {
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return home_url();
	}

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			return $GLOBALS['sitepress']->language_url( $lang );
		// qTranslate.
		case 'qtranslate':
			return qtrans_convertURL( home_url(), $lang, true );
		// qTranslate-x.
		case 'qtranslate-x':
			return qtranxf_convertURL( home_url(), $lang, true );
		// Polylang, Polylang Pro.
		case 'polylang':
			$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

			if ( ! empty( $pll->options['force_lang'] ) && isset( $pll->links ) ) {
				return pll_home_url( $lang );
			}
	}

	return home_url();
}

/**
 * Get all translated path of a specific post with ID.
 *
 * @since 2.4
 *
 * @param  int    $post_id   Post ID.
 * @param  string $post_type Post Type.
 * @param  string $regex     Regex to include at the end.
 * @return array
 */
function get_rocket_i18n_translated_post_urls( $post_id, $post_type = 'page', $regex = null ) {
	$path = wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH );

	if ( empty( $path ) ) {
		return [];
	}

	$i18n_plugin = rocket_has_i18n();
	$urls        = [];

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			$langs = get_rocket_i18n_code();

			if ( $langs ) {
				foreach ( $langs as $lang ) {
					$urls[] = wp_parse_url( get_permalink( icl_object_id( $post_id, $post_type, true, $lang ) ), PHP_URL_PATH ) . $regex;
				}
			}
			break;
		// qTranslate & qTranslate-x.
		case 'qtranslate':
		case 'qtranslate-x':
			$langs  = $GLOBALS['q_config']['enabled_languages'];
			$langs  = array_diff( $langs, array( $GLOBALS['q_config']['default_language'] ) );
			$urls[] = wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;

			if ( $langs ) {
				$url = get_permalink( $post_id );

				foreach ( $langs as $lang ) {
					if ( 'qtranslate' === $i18n_plugin ) {
						$urls[] = wp_parse_url( qtrans_convertURL( $url, $lang, true ), PHP_URL_PATH ) . $regex;
					} elseif ( 'qtranslate-x' === $i18n_plugin ) {
						$urls[] = wp_parse_url( qtranxf_convertURL( $url, $lang, true ), PHP_URL_PATH ) . $regex;
					}
				}
			}
			break;
		// Polylang.
		case 'polylang':
			if ( function_exists( 'PLL' ) && is_object( PLL()->model ) ) {
				$translations = pll_get_post_translations( $post_id );
			} elseif ( ! empty( $GLOBALS['polylang']->model ) && is_object( $GLOBALS['polylang']->model ) ) {
				$translations = $GLOBALS['polylang']->model->get_translations( 'page', $post_id );
			}

			if ( ! empty( $translations ) ) {
				foreach ( $translations as $post_id ) {
					$urls[] = wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;
				}
			}
	}

	if ( trim( $path, '/' ) !== '' ) {
		$urls[] = $path . $regex;
	}

	$urls = array_unique( $urls );

	return $urls;
}

/**
 * Returns the home URL, without WPML filters if the plugin is active
 *
 * @since 3.2.4
 * @author Remy Perona
 *
 * @param string $path Path to add to the home URL.
 * @return string
 */
function rocket_get_home_url( $path = '' ) {
	global $wpml_url_filters;
	static $home_url = [];
	static $has_wpml;

	if ( isset( $home_url[ $path ] ) ) {
		return $home_url[ $path ];
	}

	if ( ! isset( $has_wpml ) ) {
		$has_wpml = $wpml_url_filters && is_object( $wpml_url_filters ) && method_exists( $wpml_url_filters, 'home_url_filter' );
	}

	if ( $has_wpml ) {
		remove_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10 );
	}

	$home_url[ $path ] = home_url( $path );

	if ( $has_wpml ) {
		add_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10, 4 );
	}

	return $home_url[ $path ];
}
