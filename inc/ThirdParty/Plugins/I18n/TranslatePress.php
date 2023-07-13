<?php
namespace WP_Rocket\ThirdParty\Plugins\I18n;

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
			'rocket_rucss_is_home_url' => [ 'detect_homepage', 10, 2 ],
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

		$url_converter = new TRP_Url_Converter( ( new TRP_Settings() )->get_settings() );
		$language      = $url_converter->get_lang_from_url_string( $url );

		$url_language = $url_converter->get_url_for_language( $language, home_url() );

		return untrailingslashit( $url ) === untrailingslashit( $url_language ) ? $url : $home_url;
	}
}
