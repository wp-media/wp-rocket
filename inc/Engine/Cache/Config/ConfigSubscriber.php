<?php
namespace WP_Rocket\Engine\Cache\Config;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the Cache Config
 */
class ConfigSubscriber implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'permalink_structure_changed'        => 'regenerate_config_file',
			'get_rocket_option_cache_reject_uri' => 'match_pattern_with_permalink_structure',
		];
	}

	/**
	 * Returns a matching user added patterns with permalink structure
	 *
	 * @param array $patterns user pattern.
	 * @return array
	 */
	public function match_pattern_with_permalink_structure( array $patterns ): array {
		if ( empty( $patterns ) ) {
			return $patterns;
		}

		$patterns = array_map(
			function ( $uri ) {
				return user_trailingslashit( $uri );
			},
			$patterns
			);

		return $patterns;
	}

	/**
	 * Regenerate config file.
	 *
	 * @return void
	 */
	public function regenerate_config_file() {
		rocket_generate_config_file();
	}
}
