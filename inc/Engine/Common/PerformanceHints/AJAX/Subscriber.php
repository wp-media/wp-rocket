<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Processor Instance.
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Instantiate the class
	 *
	 * @param Processor $processor Processor Instance.
	 */
	public function __construct( Processor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_ajax_rocket_beacon'              => 'add_data',
			'wp_ajax_nopriv_rocket_beacon'       => 'add_data',
			'wp_ajax_rocket_check_beacon'        => 'check_data',
			'wp_ajax_nopriv_rocket_check_beacon' => 'check_data',
			'wp_ajax_rocket_beacon_nonce'        => 'add_nonce',
			'wp_ajax_nopriv_rocket_beacon_nonce' => 'add_nonce',
		];
	}

	/**
	 * Callback for data received from beacon script
	 *
	 * @return void
	 */
	public function add_data() {
		$this->processor->add_data();
	}

	/**
	 * Callback for checking data
	 *
	 * @return void
	 */
	public function check_data() {
		$this->processor->check_data();
	}

	/**
	 * Callback for returning a fresh nonce to the beacon script.
	 *
	 * The nonce embedded in the cached page HTML is generated at page render time and
	 * expires after 12-24 hours, while the page cache can live much longer. This lets
	 * the beacon recover from a 403 on the check/save endpoints by fetching a fresh
	 * nonce instead of failing for the whole lifetime of the cache file.
	 *
	 * @return void
	 */
	public function add_nonce() {
		wp_send_json_success(
			[
				'nonce' => wp_create_nonce( 'rocket_beacon' ),
			]
		);
	}
}
