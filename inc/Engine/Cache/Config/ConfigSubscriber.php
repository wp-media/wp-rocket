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
			'permalink_structure_changed'         => 'regenerate_config_file',
			'pre_update_option_' . WP_ROCKET_SLUG => [ 'change_cache_reject_uri_with_permalink', 10, 2 ],
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
		$cache_reject_uri = $this->match_pattern_with_permalink_structure( get_rocket_option( 'cache_reject_uri', [] ) );
		update_rocket_option( 'cache_reject_uri', $cache_reject_uri );

		rocket_generate_config_file();
	}

	/**
	 * Modify cache_reject_uri values.
	 *
	 * @param array|mixed $value New values.
	 * @param array|mixed $old_value Old values.
	 * @return array
	 */
	public function change_cache_reject_uri_with_permalink( $value, $old_value ): array {
		if ( ! isset( $old_value['cache_reject_uri'], $value['cache_reject_uri'] ) ) {
			return $value;
		}

		if ( $old_value['cache_reject_uri'] === $value['cache_reject_uri'] ) {
			return $value;
		}

		$value['cache_reject_uri'] = $this->match_pattern_with_permalink_structure( $value['cache_reject_uri'] );
		return $value;
	}
}
