<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Resource object.
	 *
	 * @var ResourceFetcher
	 */
	private $resource_fetcher;

	/**
	 * Scanner instance
	 *
	 * @var Scanner
	 */
	private $scanner;

	/**
	 * Status Checker instance
	 *
	 * @var StatusChecker
	 */
	private $status_checker;

	/**
	 * Subscriber constructor.
	 *
	 * @param Options_Data    $options Options instance.
	 * @param ResourceFetcher $resource_fetcher Resource object.
	 * @param Scanner         $scanner Scanner instance.
	 */
	public function __construct( Options_Data $options, ResourceFetcher $resource_fetcher, Scanner $scanner, StatusChecker $status_checker ) {
		$this->resource_fetcher = $resource_fetcher;
		$this->options          = $options;
		$this->scanner          = $scanner;
		$this->status_checker   = $status_checker;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer' => [ 'collect_resources', 11 ],
			'init'          => 'check_warmup_status',
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => [ 'start_scanner', 15, 2 ],
		];
	}

	/**
	 * Collect resources and cache them into the DB.
	 *
	 * @param string $html Page HTML.
	 *
	 * @return string
	 */
	public function collect_resources( $html ) {
		if ( $this->is_allowed() ) {
			$this->resource_fetcher->data(
				[
					'html' => $html,
				]
			)->dispatch();
		}

		return $html;
	}

	/**
	 * Launches the scanner when activating the RUCSS option
	 *
	 * @since 3.9
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 *
	 * @return void
	 */
	public function start_scanner( $old_value, $value ) {
		$this->scanner->start_scanner( $old_value, $value );
	}

	/**
	 * Checks the warmup status for resources in the prewarmup process
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function check_warmup_status() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$this->status_checker->check_warmup_status();
	}

	/**
	 * If it's allowed to warmup resources.
	 *
	 * @return bool
	 */
	private function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_bypass() ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'remove_unused_css' );
	}

}
