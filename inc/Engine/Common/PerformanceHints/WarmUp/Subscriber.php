<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\WarmUp;

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
	 * @param Controller $controller WarmUp controller instance.
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
			'wp_rocket_upgrade'      => [ 'warm_up_on_update', 10, 2 ],
			'rocket_after_clear_atf' => 'warm_up_home',
			'rocket_job_warmup'      => 'warm_up',
			'rocket_job_warmup_url'  => 'send_to_saas',
		];
	}

	/**
	 * Send home to warmup and start async fetch links
	 *
	 * @return void
	 */
	public function warm_up_home(): void {
		$this->controller->warm_up_home();
	}

	/**
	 * Fetch links for warmup and create async tasks
	 *
	 * @return void
	 */
	public function warm_up(): void {
		$this->controller->warm_up();
	}

	/**
	 * Send url to SaaS for warmup
	 *
	 * @param string $url URL to be sent.
	 *
	 * @return void
	 */
	public function send_to_saas( string $url ): void {
		$this->controller->send_to_saas( $url );
	}

	/**
	 * Process links fetched from homepage on update.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function warm_up_on_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.16', '>=' ) ) {
			return;
		}
		$this->controller->warm_up();
	}
}
