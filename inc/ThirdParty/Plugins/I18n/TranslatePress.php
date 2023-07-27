<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use TRP_Translate_Press;
use TRP_Url_Converter;
use TRP_Settings;
use WP_Rocket\Event_Management\Subscriber_Interface;

class TranslatePress implements Subscriber_Interface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'TRP_Url_Converter' ) || ! class_exists( 'TRP_Settings' ) ) {
			return [];
		}

		return [
			'rocket_rucss_is_home_url'         => [ 'detect_homepage', 10, 2 ],
			'rocket_has_i18n'                  => 'is_translatepress',
			'rocket_i18n_admin_bar_menu'       => 'add_langs_to_admin_bar',
			'rocket_i18n_current_language'     => 'set_current_language',
			'rocket_get_i18n_uri'              => 'get_active_languages_uri',
			'rocket_i18n_subdomains'           => 'get_active_languages_uri',
			'rocket_i18n_home_url'             => [ 'get_home_url_for_lang', 10, 2 ],
			'rocket_i18n_translated_post_urls' => [ 'get_translated_post_urls', 10, 4 ],
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

		$language      = $converter->get_lang_from_url_string( $url );
		$url_language  = $converter->get_url_for_language( $language, home_url() );

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
				'code'   => $code,
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
	public function get_active_languages_uri( $urls ) {
		if ( ! is_array( $urls ) ) {
			$urls = (array) $urls;
		}

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

		return $converter->get_url_for_language( $lang, $home_url );
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
		if ( ! is_array( $urls ) ) {
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
}
