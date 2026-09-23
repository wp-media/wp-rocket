<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use TRP_Translate_Press;
use WP_Rocket\Event_Management\Subscriber_Interface;

class TranslatePress implements Subscriber_Interface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'TRP_Translate_Press' ) ) {
			return [];
		}

		return [
			'rocket_saas_is_home_url'                      => [ 'detect_homepage', 10, 2 ],
			'rocket_has_i18n'                              => 'is_translatepress',
			'rocket_i18n_admin_bar_menu'                   => 'add_langs_to_admin_bar',
			'rocket_i18n_current_language'                 => 'set_current_language',
			'rocket_get_i18n_uri'                          => 'get_active_languages_uri',
			'rocket_get_i18n_code'                         => 'get_active_languages_codes',
			'rocket_i18n_subdomains'                       => 'get_active_languages_uri',
			'rocket_i18n_home_url'                         => [ 'get_home_url_for_lang', 10, 2 ],
			'rocket_i18n_translated_post_urls'             => [ 'get_translated_post_urls', 10, 4 ],
			'post_updated'                                 => 'clear_post_languages',
			'trp_save_editor_translations_regular_strings' => [ 'clear_post_after_updating_translation', 10, 2 ],
			'rocket_current_url'                           => 'adjust_current_url',
			'rocket_buffer'                                => [ 'clean_hreflang_query_strings', 1000 ],
		];
	}

	/**
	 * Detect homepage.
	 *
	 * @param string $home_url home url.
	 * @param string $url url of current page.
	 * @return string
	 */
	public function detect_homepage( $home_url, $url ) {

		$translatepress = TRP_Translate_Press::get_trp_instance();
		$converter      = $translatepress->get_component( 'url_converter' );

		$language     = $converter->get_lang_from_url_string( $url );
		$url_language = $converter->get_url_for_language( $language, home_url() );

		return untrailingslashit( $url ) === untrailingslashit( $url_language ) ? $url : $home_url;
	}

	/**
	 * Adds TranslatePress as identifier for i18n detection
	 *
	 * @param string|bool $identifier An identifier value, false otherwise.
	 *
	 * @return string|bool
	 */
	public function is_translatepress( $identifier ) {
		if (
			function_exists( 'trp_get_languages' )
			&&
			! empty( trp_get_languages( 'nodefault' ) )
		) {
			return 'translatepress';
		}

		return $identifier;
	}

	/**
	 * Adds languages to the admin bar menu
	 *
	 * @param array $langlinks Array of languages.
	 *
	 * @return array
	 */
	public function add_langs_to_admin_bar( $langlinks ) {
		$translatepress = TRP_Translate_Press::get_trp_instance();

		$language_switcher = $translatepress->get_component( 'language_switcher' );
		$settings          = $translatepress->get_component( 'settings' );
		$languages         = $translatepress->get_component( 'languages' );
		$trp_settings      = $settings->get_settings();

		$languages_to_display = $trp_settings['publish-languages'];
		$published_languages  = $languages->get_language_names( $languages_to_display );

		foreach ( $published_languages as $code => $name ) {
			$langlinks[ $code ] = [
				'code'   => $trp_settings['url-slugs'][ $code ],
				'flag'   => $language_switcher->add_flag( $code, $name ),
				'anchor' => $name,
			];
		}

		return $langlinks;
	}

	/**
	 * Sets the current language value
	 *
	 * @param string|bool $current_language Current language.
	 *
	 * @return string|bool
	 */
	public function set_current_language( $current_language ) {
		if ( empty( $GLOBALS['TRP_LANGUAGE'] ) ) {
			return $current_language;
		}

		return $GLOBALS['TRP_LANGUAGE'];
	}

	/**
	 * Gets URLs for active languages
	 *
	 * @param array $urls Array of active languages URI.
	 *
	 * @return array
	 */
	public function get_active_languages_uri( array $urls ): array {
		$home_url = home_url();

		$translatepress = TRP_Translate_Press::get_trp_instance();

		$settings     = $translatepress->get_component( 'settings' );
		$languages    = $translatepress->get_component( 'languages' );
		$converter    = $translatepress->get_component( 'url_converter' );
		$trp_settings = $settings->get_settings();

		$languages_to_display = $trp_settings['publish-languages'];
		$published_languages  = $languages->get_language_names( $languages_to_display );

		foreach ( $published_languages as $code => $name ) {
			$urls[] = $converter->get_url_for_language( $code, $home_url );
		}

		return $urls;
	}

	/**
	 * Gets the active languages slugs
	 *
	 * @param array $codes Array of languages codes.
	 *
	 * @return array
	 */
	public function get_active_languages_codes( $codes ) {
		if ( ! is_array( $codes ) ) { // @phpstan-ignore-line
			$codes = (array) $codes;
		}

		$translatepress = TRP_Translate_Press::get_trp_instance();

		$settings     = $translatepress->get_component( 'settings' );
		$languages    = $translatepress->get_component( 'languages' );
		$trp_settings = $settings->get_settings();

		$languages_to_display = $trp_settings['publish-languages'];
		$published_languages  = $languages->get_language_names( $languages_to_display );

		foreach ( $published_languages as $code => $name ) {
			$codes[] = $trp_settings['url-slugs'][ $code ];
		}

		return $codes;
	}

	/**
	 * Gets home URL in given language
	 *
	 * @param string $home_url Home URL.
	 * @param string $lang Language code.
	 *
	 * @return string
	 */
	public function get_home_url_for_lang( $home_url, $lang ) {
		if ( empty( $lang ) ) {
			return $home_url;
		}

		$translatepress = TRP_Translate_Press::get_trp_instance();
		$converter      = $translatepress->get_component( 'url_converter' );
		$settings       = $translatepress->get_component( 'settings' );
		$trp_settings   = $settings->get_settings();

		$code = '';

		add_filter( 'trp_add_language_to_home_url_check_for_admin', '__return_false' );

		foreach ( $trp_settings['url-slugs'] as $index => $slug ) {
			if ( $lang === $slug ) {
				$code = $index;
				break;
			}
		}

		$url = $converter->get_url_for_language( $code, $home_url );

		remove_filter( 'trp_add_language_to_home_url_check_for_admin', '__return_false' );

		return $url;
	}

	/**
	 * Gets all translations URLs for a post
	 *
	 * @param array  $urls Array of translated URLs.
	 * @param string $url URL to use.
	 * @param string $post_type Post type.
	 * @param string $regex Pattern to include at the end.
	 *
	 * @return array
	 */
	public function get_translated_post_urls( $urls, $url, $post_type, $regex ) {
		if ( ! is_array( $urls ) ) { // @phpstan-ignore-line
			$urls = (array) $urls;
		}

		$translatepress = TRP_Translate_Press::get_trp_instance();

		$settings     = $translatepress->get_component( 'settings' );
		$languages    = $translatepress->get_component( 'languages' );
		$converter    = $translatepress->get_component( 'url_converter' );
		$trp_settings = $settings->get_settings();

		$languages_to_display = $trp_settings['publish-languages'];
		$published_languages  = $languages->get_language_names( $languages_to_display );

		foreach ( $published_languages as $code => $name ) {
			$urls[] = wp_parse_url( $converter->get_url_for_language( $code, $url ), PHP_URL_PATH ) . $regex;
		}

		return $urls;
	}

	/**
	 * Clear all languages of a specific post
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function clear_post_languages( $post_id ) {
		$translatepress = TRP_Translate_Press::get_trp_instance();

		$converter    = $translatepress->get_component( 'url_converter' );
		$settings     = $translatepress->get_component( 'settings' );
		$trp_settings = $settings->get_settings();

		add_filter( 'trp_add_language_to_home_url_check_for_admin', '__return_false' );

		$clear_urls = [];

		$default_permalink = get_permalink( $post_id );

		foreach ( $trp_settings['translation-languages'] as $language ) {
			if ( $language === $trp_settings['default-language'] ) {
				continue;
			}

			$clear_urls[] = $converter->get_url_for_language( $language, $default_permalink, '' );
		}

		remove_filter( 'trp_add_language_to_home_url_check_for_admin', '__return_false' );

		if ( empty( $clear_urls ) ) {
			return;
		}

		rocket_clean_files( $clear_urls );
	}

	/**
	 * Clear the post cache when the translation is updated
	 *
	 * @param array $update_strings Array of updated strings.
	 * @param array $settings Array of settings.
	 *
	 * @return void
	 */
	public function clear_post_after_updating_translation( $update_strings, $settings ) {
		$translatepress = TRP_Translate_Press::get_trp_instance();

		$converter = $translatepress->get_component( 'url_converter' );

		if ( empty( $_POST['url'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$url = esc_url_raw( wp_unslash( $_POST['url'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$clear_urls  = [];
		$current_url = remove_query_arg( 'trp-edit-translation', $url );

		foreach ( $settings['translation-languages'] as $language ) {
			if ( ! empty( $update_strings[ $language ] ) ) {
				$clear_urls[] = $converter->get_url_for_language( $language, $current_url, '' );
			}
		}

		rocket_clean_files( $clear_urls );
	}

	/**
	 * Adjusts the current URL to match the language-specific URL format used by TranslatePress.
	 *
	 * Removes the '#TRPLINKPROCESSED' marker and returns the correct URL for the detected language.
	 *
	 * @param string $current_url The current URL to adjust.
	 * @return string The adjusted URL for the current language.
	 */
	public function adjust_current_url( $current_url ) {
		$translatepress = \TRP_Translate_Press::get_trp_instance();
		$converter      = $translatepress->get_component( 'url_converter' );
		$language       = $converter->get_lang_from_url_string( $current_url );

		return str_replace( '#TRPLINKPROCESSED', '', $converter->get_url_for_language( $language, $current_url ) );
	}

	/**
	 * Removes ignored query strings from hreflang tags in the HTML output.
	 *
	 * When TranslatePress generates hreflang tags, it may include query parameters
	 * that should be ignored during caching. This method cleans those parameters
	 * from the href attributes in hreflang link tags.
	 *
	 * @param string $buffer HTML content to process.
	 * @return string Processed HTML content with clean hreflang URLs.
	 */
	public function clean_hreflang_query_strings( $buffer ) {
		$ignored_params = rocket_get_ignored_parameters();

		if ( empty( $ignored_params ) ) {
			return $buffer;
		}

		$pattern = '/<link\s+[^>]*hreflang=["\'][^>]*>/i';

		return preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $ignored_params ) {
				$tag     = $matches[0];
				$cleaned = $this->remove_ignored_params_from_href( $tag, $ignored_params );
				return $cleaned;
			},
			$buffer
		);
	}

	/**
	 * Removes ignored query parameters from the href attribute in a link tag.
	 *
	 * @param string $tag            The complete link tag.
	 * @param array  $ignored_params Array of query parameter names to ignore.
	 * @return string The link tag with cleaned href attribute.
	 */
	private function remove_ignored_params_from_href( $tag, $ignored_params ) {
		$href_pattern = '/href=["\']([^"\']+)["\']/i';

		return preg_replace_callback(
			$href_pattern,
			function ( $matches ) use ( $ignored_params ) {
				$url       = $matches[1];
				$cleaned_url = $this->clean_url_query_string( $url, $ignored_params );
				return 'href="' . esc_attr( $cleaned_url ) . '"';
			},
			$tag
		);
	}

	/**
	 * Removes ignored query parameters from a URL.
	 *
	 * @param string $url            The URL to clean.
	 * @param array  $ignored_params Array of query parameter names to ignore.
	 * @return string The cleaned URL.
	 */
	private function clean_url_query_string( $url, $ignored_params ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['query'] ) ) {
			return $url;
		}

		wp_parse_str( $parsed_url['query'], $query_params );

		$query_params = array_diff_key( $query_params, array_flip( $ignored_params ) );

		$cleaned_query = http_build_query( $query_params );

		if ( empty( $cleaned_query ) ) {
			// Remove the query string entirely.
			$base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
			if ( isset( $parsed_url['port'] ) ) {
				$base_url .= ':' . $parsed_url['port'];
			}
			$base_url .= $parsed_url['path'];
			if ( isset( $parsed_url['fragment'] ) ) {
				$base_url .= '#' . $parsed_url['fragment'];
			}
			return $base_url;
		}

		// Rebuild URL with cleaned query string.
		$new_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		if ( isset( $parsed_url['port'] ) ) {
			$new_url .= ':' . $parsed_url['port'];
		}
		$new_url .= $parsed_url['path'];
		$new_url .= '?' . $cleaned_query;
		if ( isset( $parsed_url['fragment'] ) ) {
			$new_url .= '#' . $parsed_url['fragment'];
		}

		return $new_url;
	}
}
