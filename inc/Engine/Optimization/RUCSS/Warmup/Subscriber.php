<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\RESTWP;
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
	 * Subscriber constructor.
	 *
	 * @param Options_Data    $options Options instance.
	 * @param ResourceFetcher $resource_fetcher Resource object.
	 * @param RESTWP $restwp RESTWP instance.
	 */
	public function __construct( Options_Data $options, ResourceFetcher $resource_fetcher, RESTWP $restwp ) {
		$this->resource_fetcher = $resource_fetcher;
		$this->options          = $options;
		$this->restwp = $restwp;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer' => [ 'collect_resources', 1 ],
			'rest_api_init' => 'register_routes',
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
