<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for Avada theme
 */
class Avada implements Subscriber_Interface {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.1
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_avada() ) {
			return [];
		}

		return [
			'avada_clear_dynamic_css_cache' => 'clean_domain',
			'rocket_exclude_defer_js'       => 'exclude_defer_js',
			'fusion_cache_reset_after'      => 'clean_domain',
		];
	}

	/**
	 * Check if is Avada theme.
	 *
	 * @return boolean
	 */
	private static function is_avada() {
		$current_theme = wp_get_theme();
		return 'avada' === strtolower( $current_theme->get( 'Name' ) ) || 'avada' === strtolower( $current_theme->get_template() );
	}

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * When Avada theme purge its own cache.
	 * Clear WP Rocket cache when Avada dynamic CSS is updated.
	 *
	 * @return void
	 */
	public function clean_domain() {
		rocket_clean_domain();
	}

	/**
	 * Excludes Avada Google Maps JS files from defer JS
	 *
	 * @param array $exclude_defer_js Array of JS filepaths to be excluded.
	 * @return array
	 */
	public function exclude_defer_js( $exclude_defer_js ) {
		$exclude_defer_js[] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';
		$exclude_defer_js[] = 'maps.googleapis.com';

		return $exclude_defer_js;
	}
}
