<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get all langs to display in admin bar for WPML
 *
 * @since 1.3.0
 *
 * @return array $langlinks List of active languages
 */
function get_rocket_wpml_langs_for_admin_bar() {  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	global $sitepress;
	$langlinks = [];

	// Making all languages the first option either when it's set or not.
	$langlinks[] = [
		'code'    => 'all',
		'current' => 'all' === $sitepress->get_current_language(),
		'anchor'  => __( 'All languages', 'rocket' ),
		'flag'    => '<img class="icl_als_iclflag" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" alt="all" width="16" height="16" />',
	];

	foreach ( $sitepress->get_active_languages() as $lang ) {
		// Get flag.
		$flag     = $sitepress->get_flag( $lang['code'] );
		$flag_url = ICL_PLUGIN_URL . '/res/flags/' . $flag->flag;

		if ( $flag->from_template ) {
			$wp_upload_dir = wp_upload_dir();
			$flag_url      = $wp_upload_dir['baseurl'] . '/flags/' . $flag->flag;
		}

		$langlinks[] = [
			'code'    => $lang['code'],
			'current' => $lang['code'] === $sitepress->get_current_language(),
			'anchor'  => $lang['display_name'],
			'flag'    => '<img class="icl_als_iclflag" src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $lang['code'] ) . '" width="18" height="12" />',
		];
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
function get_rocket_qtranslate_langs_for_admin_bar( $fork = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	global $q_config;

	$langlinks   = [];
	$currentlang = [];

	foreach ( $q_config['enabled_languages'] as $lang ) {

		$langlinks[ $lang ] = [
			'code'   => $lang,
			'anchor' => $q_config['language_name'][ $lang ],
			'flag'   => '<img src="' . esc_url( trailingslashit( WP_CONTENT_URL ) . $q_config['flag_location'] . $q_config['flag'][ $lang ] ) . '" alt="' . esc_attr( $q_config['language_name'][ $lang ] ) . '" width="18" height="12" />',
		];

	}

	if ( isset( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$lang = sanitize_key( $_GET['lang'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'x' === $fork ) {
			if ( qtranxf_isEnabled( $lang ) ) {
				$currentlang[ $lang ] = $langlinks[ $lang ];
				unset( $langlinks[ $lang ] );
				$langlinks = $currentlang + $langlinks;
			}
		} elseif ( qtrans_isEnabled( $lang ) ) {
			$currentlang[ $lang ] = $langlinks[ $lang ];
			unset( $langlinks[ $lang ] );
			$langlinks = $currentlang + $langlinks;
		}
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
function get_rocket_polylang_langs_for_admin_bar() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	global $polylang;

	$langlinks   = [];
	$currentlang = [];
	$langs       = [];
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
					$currentlang[ $lang->slug ] = [
						'code'   => $lang->slug,
						'anchor' => $lang->name,
						'flag'   => $img,
					];
				} else {
					$langlinks[ $lang->slug ] = [
						'code'   => $lang->slug,
						'anchor' => $lang->name,
						'flag'   => $img,
					];
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
 * @return string An identifier corresponding to the active plugin.
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
			return '';
		}

		// Polylang, Polylang Pro.
		return 'polylang';
	}

	if ( ! empty( $q_config ) && is_array( $q_config ) ) {
		if ( function_exists( 'qtranxf_convertURL' ) ) {
			// qTranslate-x.
			return 'qtranslate-x';
		}

		if ( function_exists( 'qtrans_convertURL' ) ) {
			// qTranslate.
			return 'qtranslate';
		}
	}

	$identifier = '';
	$default    = $identifier;

	/**
	 * Filters the value of i18n plugin detection
	 *
	 * @param string $identifier An identifier value.
	 */
	$identifier = apply_filters( 'rocket_has_i18n', $identifier );

	if ( ! is_string( $identifier ) ) {
		$identifier = $default;
	}

	return $identifier;
}

/**
 * Get infos of all active languages.
 *
 * @since 2.0
 *
 * @return array A list of language codes.
 */
function get_rocket_i18n_code() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return [];
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

	$codes   = [];
	$default = $codes;

	/**
	 * Filters the active languages codes list
	 *
	 * @param array $codes Array of languages codes.
	 */
	$codes = apply_filters( 'rocket_get_i18n_code', $codes );

	if ( ! is_array( $codes ) ) {
		$codes = $default;
	}

	return $codes;
}

/**
 * Get all active languages host
 *
 * @since 2.6.8
 *
 * @return array $urls List of all active languages host
 */
function get_rocket_i18n_host() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$langs_host = [];
	$langs      = get_rocket_i18n_uri();

	if ( empty( $langs ) ) {
		return $langs_host;
	}

	foreach ( $langs as $lang ) {
		$langs_host[] = wp_parse_url( $lang, PHP_URL_HOST );
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
function get_rocket_i18n_uri() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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
			if ( ! defined( 'POLYLANG_VERSION' ) || version_compare( POLYLANG_VERSION, '3.4', '<' ) ) {
				$urls = wp_list_pluck( $pll->model->get_languages_list(), 'search_url' );
			}else {
				$languages = $pll->model->get_languages_list();
				foreach ( $languages as $language ) {
					$urls[] = $language->get_home_url();
				}
			}
		}
	}

	/**
	 * Filters the value of all active languages URI
	 *
	 * @param array Array of active languages URI.
	 */
	$urls = apply_filters( 'rocket_get_i18n_uri', $urls );

	if (
		! is_array( $urls )
		||
		empty( $urls )
	) {
		$urls[] = home_url();
	}

	return $urls;
}

/**
 * Get directories paths to preserve languages ​​when purging a domain.
 * This function is required when the domains of languages (​​other than the default) are managed by subdirectories.
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/
 * and /de/ are deleted. But, if you have a domain for your english and german websites with example.com/en/ and
 * example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 3.5.5 Normalize paths + micro-optimization by passing in the cache path.
 * @since 2.0
 *
 * @param string $current_lang The current language code.
 * @param string $cache_path   Optional. WP Rocket's cache path.
 *
 * @return array                A list of directories path to preserve.
 */
function get_rocket_i18n_to_preserve( $current_lang, $cache_path = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
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
	if ( empty( $langs ) ) {
		return [];
	}

	// Remove current lang to the preserve dirs.
	$langs = array_diff( $langs, [ $current_lang ] );

	if ( '' === $cache_path ) {
		$cache_path = _rocket_get_wp_rocket_cache_path();
	}

	// Stock all URLs of langs to preserve.
	$langs_to_preserve = [];
	foreach ( $langs as $lang ) {
		$parse_url           = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
		$langs_to_preserve[] = _rocket_normalize_path(
			"{$cache_path}{$parse_url['host']}(.*)/" . trim( $parse_url['path'], '/' ),
			true // escape directory separators for regex.
		);
	}

	/**
	 * Filter directories path to preserve of cache purge.
	 *
	 * @since 2.1
	 *
	 * @param array $langs_to_preserve List of directories path to preserve.
	 */
	return (array) apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
}

/**
 * Get all languages subdomains URLs
 *
 * @since 2.1
 *
 * @return array $urls List of languages subdomains URLs
 */
function get_rocket_i18n_subdomains() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return [];
	}

	$urls    = [];
	$default = $urls;

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			$option = get_option( 'icl_sitepress_settings' );

			if ( 2 === (int) $option['language_negotiation_type'] ) {
				$urls = get_rocket_i18n_uri();
			}
			break;
		// qTranslate.
		case 'qtranslate':
			if ( 3 === (int) $GLOBALS['q_config']['url_mode'] ) {
				$urls = get_rocket_i18n_uri();
			}
			break;
		// qTranslate-x.
		case 'qtranslate-x':
			if ( 3 === (int) $GLOBALS['q_config']['url_mode'] || 4 === (int) $GLOBALS['q_config']['url_mode'] ) {
				$urls = get_rocket_i18n_uri();
			}
			break;
		// Polylang, Polylang Pro.
		case 'polylang':
			$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

			if ( ! empty( $pll ) && is_object( $pll ) && ( 2 === (int) $pll->options['force_lang'] || 3 === (int) $pll->options['force_lang'] ) ) {
				$urls = get_rocket_i18n_uri();
			}
			break;
		default:
			/**
			 * Filters the list of languages subdomains URLs
			 *
			 * @param array $urls Array of languages subdomains URLs.
			 */
			$urls = apply_filters( 'rocket_i18n_subdomains', $urls );

			if ( ! is_array( $urls ) ) {
				$urls = $default;
			}
	}

	return $urls;
}

/**
 * Get home URL of a specific lang.
 *
 * @since 2.2
 *
 * @param  string $lang The language code. Default is an empty string.
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();

	$home_url = home_url();
	$default  = $home_url;

	if ( ! $i18n_plugin ) {
		return $home_url;
	}

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			$home_url = $GLOBALS['sitepress']->language_url( $lang );
			break;
			// qTranslate.
		case 'qtranslate':
			$home_url = qtrans_convertURL( home_url(), $lang, true );
			break;
			// qTranslate-x.
		case 'qtranslate-x':
			$home_url = qtranxf_convertURL( home_url(), $lang, true );
			break;
			// Polylang, Polylang Pro.
		case 'polylang':
			$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

			if ( ! empty( $pll->options['force_lang'] ) && isset( $pll->links ) ) {
				$home_url = pll_home_url( $lang );
			}
			break;
		default:
			/**
			 * Filters the home URL value for a specific language
			 *
			 * @param string $home_url Home URL.
			 * @param string $lang     The language code.
			 */
			$home_url = apply_filters( 'rocket_i18n_home_url', $home_url, $lang );

			if ( ! is_string( $home_url ) ) {
				$home_url = $default;
			}
	}

	return $home_url;
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
function get_rocket_i18n_translated_post_urls( $post_id, $post_type = 'page', $regex = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$url  = get_permalink( $post_id );
	$path = wp_parse_url( $url, PHP_URL_PATH );

	if ( empty( $path ) ) {
		return [];
	}

	$i18n_plugin = rocket_has_i18n();

	$urls    = [];
	$default = $urls;

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
			$langs  = array_diff( $langs, [ $GLOBALS['q_config']['default_language'] ] );
			$urls[] = wp_parse_url( $url, PHP_URL_PATH ) . $regex;

			if ( $langs ) {
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
				foreach ( $translations as $translation_post_id ) {
					$urls[] = wp_parse_url( get_permalink( $translation_post_id ), PHP_URL_PATH ) . $regex;
				}
			}
			break;
		default:
			/**
			 * Filters the list of translated URLs for a post ID
			 *
			 * @param array  $urls Array of translated URLs.
			 * @param string $url URL to use.
			 * @param string $post_type Post type.
			 * @param string $regex Pattern to include at the end.
			 */
			$urls = apply_filters( 'rocket_i18n_translated_post_urls', $urls, $url, $post_type, $regex );

			if ( ! is_array( $urls ) ) {
				$urls = $default;
			}
	}

	if ( trim( $path, '/' ) !== '' ) {
		$urls[] = $path . $regex;
	}

	return array_unique( $urls );
}

/**
 * Returns the home URL, without WPML filters if the plugin is active
 *
 * @since 3.2.4
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

/**
 * Gets the current language
 *
 * @since 3.3.3
 *
 * @return string
 */
function rocket_get_current_language() {
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return '';
	}

	$current_language = '';
	$default          = $current_language;

	if ( 'polylang' === $i18n_plugin && function_exists( 'pll_current_language' ) ) {
		return pll_current_language();
	} elseif ( 'wpml' === $i18n_plugin ) {
		return apply_filters( 'wpml_current_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Filters the current language value
	 *
	 * @param string $current_language Current language.
	 */
	$current_language = apply_filters( 'rocket_i18n_current_language', $current_language );

	if ( ! is_string( $current_language ) ) {
		$current_language = $default;
	}

	return $current_language;
}
