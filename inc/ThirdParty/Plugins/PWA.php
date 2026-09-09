<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

class PWA implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether the PWA plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return function_exists( 'wp_get_service_worker_url' );
	}

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_cache_reject_uri' => 'exclude_service_worker',
		];
	}

	/**
	 * Excludes the PWA service worker URL
	 *
	 * @param array $excluded Array of excluded URLs.
	 *
	 * @return array
	 */
	public function exclude_service_worker( $excluded ): array {
		if ( ! function_exists( 'wp_get_service_worker_url' ) ) {
			return $excluded;
		}

		$excluded[] = '/wp.serviceworker/?';

		return $excluded;
	}
}
