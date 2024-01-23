<?php

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

class RocketLazyLoad implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		if ( ! defined( 'ROCKET_LL_VERSION' ) ) {
			return [];
		}

		return [
			'rocket_delay_js_exclusions' => 'exclude_rocket_lazyload_script',
		];
	}

	/**
	 * Excludes rocket lazyload script from delay js.
	 *
	 * @param array $excluded List of excluded files.
	 *
	 * @return array
	 */
	public function exclude_rocket_lazyload_script( $excluded ): array {
		$excluded[] = 'rocket-lazy-load/assets/js/\d\d\.\d/lazyload.min.js';
		return $excluded;
	}
}
