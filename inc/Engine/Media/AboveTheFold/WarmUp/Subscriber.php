<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * WarmUp controller instance
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @param Controller $controller ATF WarmUp controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_upgrade'      => [ 'process_links_on_update', 10, 2 ],
			'rocket_after_clear_atf' => 'process_links',
		];
	}

	/**
	 * Process links fetched from homepage.
	 *
	 * @return void
	 */
	public function process_links(): void {
		$this->controller->process_links();
	}

	/**
	 * Process links fetched from homepage on update.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function process_links_on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.16', '>=' ) ) {
			return;
		}
		$this->controller->process_links();
	}
}
