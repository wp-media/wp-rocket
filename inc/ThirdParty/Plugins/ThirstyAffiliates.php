<?php
declare( strict_types=1 );

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class ThirstyAffiliates implements Subscriber_Interface {
	/**
	 * Returns an array of events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_preload_links_exclusions' => [ 'exclude_link_prefix', PHP_INT_MAX, 2 ],
		];
	}

	/**
	 * Excludes the link prefix from preload links
	 *
	 * @since 3.10.8
	 *
	 * @param string[] $excluded Array of excluded patterns.
	 * @param string[] $default Array of default excluded patterns.
	 *
	 * @return array
	 */
	public function exclude_link_prefix( $excluded, $default ): array {
		if ( ! is_array( $excluded ) ) { // @phpstan-ignore-line
			$excluded = (array) $excluded;
		}

		if ( ! is_plugin_active( 'thirstyaffiliates/thirstyaffiliates.php' ) ) {
			return $excluded;
		}

		$excluded    = array_diff( $excluded, $default );
		$link_prefix = get_option( 'ta_link_prefix', 'recommends' );

		if ( 'custom' === $link_prefix ) {
			$link_prefix = get_option( 'ta_link_prefix_custom', 'recommends' );
		}

		$excluded[] = '/' . $link_prefix . '/';

		return $excluded;
	}
}
