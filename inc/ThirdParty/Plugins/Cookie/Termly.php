<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Cookie;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for Termly.
 */
class Termly implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'TERMLY_VERSION' ) ) {
			return [];
		}

		return [
			'rocket_exclude_defer_js'    => 'exclude_termly_defer_and_delay_js',
			'rocket_delay_js_exclusions' => 'exclude_termly_defer_and_delay_js',
		];
	}

	/**
	 * Defer and delay Termly Resources
	 *
	 * @param array $exclude_delay_js Array of JS to be excluded.
	 *
	 * @return array
	 */
	public function exclude_termly_defer_and_delay_js( array $exclude_delay_js ): array {
		$auto_block = get_option( 'termly_display_auto_blocker', 'off' );
		if ( 'on' !== $auto_block ) {
			return $exclude_delay_js;
		}

		$exclude_delay_js[] = 'app.termly.io/resource-blocker/(.*)';

		return $exclude_delay_js;
	}
}
