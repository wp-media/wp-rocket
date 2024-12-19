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
			'rocket_exclude_defer_js'    => 'exclude_defer_js',
			'rocket_delay_js_exclusions' => 'exclude_defer_js',
		];
	}

	/**
	 * Check if Termly auto blocker is on.
	 *
	 * @return bool
	 */
	private function should_exclude(): bool {
		$auto_block = get_option( 'termly_display_auto_blocker', 'off' );
		if ( 'on' !== $auto_block ) {
			return false;
		}

		return true;
	}

	/**
	 * Excludes Termly JS files from delay JS
	 *
	 * @param array $exclude_delay_js Array of JS to be excluded.
	 *
	 * @return array
	 */
	public function exclude_defer_js( array $exclude_delay_js ): array {
		if ( ! $this->should_exclude() ) {
			return $exclude_delay_js;
		}

		$exclude_delay_js[] = 'app.termly.io/resource-blocker/(.*)';

		return $exclude_delay_js;
	}
}
