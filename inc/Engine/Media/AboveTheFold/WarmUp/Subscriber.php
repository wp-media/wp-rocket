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
			'wp_rocket_upgrade'      	 => [ 'warm_up_on_update', 10, 2 ],
			'rocket_after_clear_atf' 	 => 'warm_up',
			'rocket_saas_api_queued_url' => 'add_wpr_imagedimensions_query_arg'
		];
	}

	/**
	 * Process links fetched from homepage.
	 *
	 * @return void
	 */
	public function warm_up(): void {
		$this->controller->warm_up();
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

	public function add_wpr_imagedimensions_query_arg() {
		return $this->controller->add_wpr_imagedimensions_query_arg();
	}
}
