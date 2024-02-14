<?php

namespace WP_Rocket\Engine\Debug;

use WP_Rocket\Event_Management\Subscriber_Interface;

class DebugSubscriber implements Subscriber_Interface {

	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_first_install' => 'on_first_install',
			'wp_rocket_upgrade'       => [ 'on_upgrade', 10, 2 ],
		];
	}

	/**
	 * Adds the debug option on first install.
	 *
	 * @return void
	 */
	public function on_first_install(): void {
		$this->add_debug_options();
	}

	/**
	 * Adds the debug option on upgrade.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function on_upgrade( $new_version, $old_version ): void {
		if ( version_compare( $old_version, '3.16', '>=' ) ) {
			return;
		}

		$this->add_debug_options();
	}

	/**
	 * Adds the debug option.
	 *
	 * @return boolean
	 */
	private function add_debug_options(): bool {
		return add_option(
			'wp_rocket_debug',
			[
				'last_rucss_job_added' => '',
			]
		);
	}
}
