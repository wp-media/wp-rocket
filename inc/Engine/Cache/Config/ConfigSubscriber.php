<?php
namespace WP_Rocket\Engine\Cache\Config;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;

/**
 * Subscriber for the Cache Config
 */
class ConfigSubscriber implements Subscriber_Interface {

	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Creates an instance of the Cache Config Subscriber.
	 *
	 * @param Options_Data $options     WP Rocket options instance.
	 * @param Options      $options_api Options instance.
	 */
	public function __construct( Options_Data $options, Options $options_api ) {
		$this->options     = $options;
		$this->options_api = $options_api;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'permalink_structure_changed' => 'regenerate_config_file',
			'pre_update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' ) => [ 'change_cache_reject_uri_with_permalink', 10, 2 ],
		];
	}

	/**
	 * Returns a matching user added patterns with permalink structure
	 *
	 * @param array $patterns user pattern.
	 * @return array
	 */
	private function match_pattern_with_permalink_structure( array $patterns ): array {
		if ( empty( $patterns ) ) {
			return $patterns;
		}

		$patterns = array_map(
			function ( $uri ) {
				if ( false !== strpos( $uri, 'index.php' ) || '/' === $uri ) {
					return $uri;
				}

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
		$cache_reject_uri = $this->match_pattern_with_permalink_structure( $this->options->get( 'cache_reject_uri', [] ) );

		$this->options->set( 'cache_reject_uri', $cache_reject_uri );
		$this->options_api->set( 'settings', $this->options->get_options() );

		rocket_generate_config_file();
	}

	/**
	 * Modify cache_reject_uri values.
	 *
	 * @param mixed $value The new, unserialized option value.
	 * @param mixed $old_value The old option value.
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
