<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\RESTWP;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\Checker;
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
	 * Resource object.
	 *
	 * @var RESTWP
	 */
	private $restwp;

	/**
	 * Scanner instance
	 *
	 * @var Scanner
	 */
	private $scanner;

	/**
	 * Status Checker instance
	 *
	 * @var Checker
	 */
	private $status_checker;

	/**
	 * Subscriber constructor.
	 *
	 * @param Options_Data    $options Options instance.
	 * @param ResourceFetcher $resource_fetcher Resource object.
	 * @param RESTWP          $restwp RESTWP instance.
	 * @param Scanner         $scanner Scanner instance.
	 * @param Checker         $status_checker Status checker instance.
	 */
	public function __construct( Options_Data $options, ResourceFetcher $resource_fetcher, RESTWP $restwp, Scanner $scanner, Checker $status_checker ) {
		$this->resource_fetcher = $resource_fetcher;
		$this->options          = $options;
		$this->restwp           = $restwp;
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
			'rest_api_init' => 'register_routes',
			'init'          => [
				[ 'check_warmup_status', 10 ],
				[ 'check_warmup_completed', 11 ],
				[ 'auto_stop_warmup_after_1hour', 12 ],
			],
			// The following priority should be less than 10.
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => [ 'start_scanner', 9, 2 ],
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

		$prewarmup_stats = get_option( 'wp_rocket_prewarmup_stats', [] );
		if ( empty( $prewarmup_stats ) || empty( $prewarmup_stats['fetch_finish_time'] ) ) {
			return;
		}

		$this->status_checker->check_warmup_status();
	}

	/**
	 * Checks the is prewarmup process is completed.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function check_warmup_completed() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$this->status_checker->check_warmup_completed();
	}

	/**
	 * Automatically stops the prewarmup process is it passed more than 1 hour.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function auto_stop_warmup_after_1hour() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$this->status_checker->auto_stop_warmup_after_1hour();
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

	/**
	 * Registers status routes in the API.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function register_routes() {
		$this->restwp->register_status_route();
	}

}
