<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\AdminBar;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Loads the stylesheet that keeps the WP Rocket admin bar item visible on mobile.
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Capabilities giving access to the WP Rocket admin bar node.
	 *
	 * Duplicated from rocket_admin_bar() (inc/common/admin-bar.php): keep both lists in sync.
	 * Here it only avoids loading the stylesheet for users who cannot see the node.
	 *
	 * @var string[]
	 */
	private const CAPABILITIES = [
		'rocket_manage_options',
		'rocket_purge_cache',
		'rocket_preload_cache',
		'rocket_regenerate_critical_css',
		'rocket_remove_unused_css',
	];

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_enqueue_scripts' => 'enqueue_admin_bar_style',
			'wp_enqueue_scripts'    => 'enqueue_admin_bar_style',
		];
	}

	/**
	 * Enqueues the admin bar stylesheet in the admin and on the front end.
	 *
	 * @return void
	 */
	public function enqueue_admin_bar_style(): void {
		if ( ! is_admin_bar_showing() || ! $this->has_capability() ) {
			return;
		}

		wp_enqueue_style(
			'wpr-admin-bar',
			rocket_get_constant( 'WP_ROCKET_ASSETS_CSS_URL' ) . 'wpr-admin-bar.css',
			[ 'admin-bar' ],
			rocket_get_constant( 'WP_ROCKET_VERSION' )
		);
	}

	/**
	 * Checks if the current user can see the WP Rocket admin bar node.
	 *
	 * @return bool
	 */
	private function has_capability(): bool {
		foreach ( self::CAPABILITIES as $capability ) {
			if ( current_user_can( $capability ) ) {
				return true;
			}
		}

		return false;
	}
}
