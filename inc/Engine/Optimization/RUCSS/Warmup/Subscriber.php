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
	 * Warmup process instance.
	 *
	 * @var WarmupProcess
	 */
	private $warmup_process;

	/**
	 * Subscriber constructor.
	 *
	 * @param Options_Data    $options Options instance.
	 * @param ResourceFetcher $resource_fetcher Resource object.
	 * @param WarmupProcess   $warmup_process Warmup Process instance.
	 */
	public function __construct( Options_Data $options, ResourceFetcher $resource_fetcher, WarmupProcess $warmup_process ) {
		$this->resource_fetcher = $resource_fetcher;
		$this->warmup_process   = $warmup_process;
		$this->options          = $options;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer'                       => 'collect_resources',
			'rocket_rucss_fetch_resources_finish' => 'call_warmup',
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
			$this->resource_fetcher->handle( $html );
		}

		return $html;
	}

	/**
	 * Dispatch the background process for calling the saas warmup.
	 *
	 * @param array $db_resources_ids Array of DB resources IDs to be sent to warmup.
	 */
	public function call_warmup( array $db_resources_ids = [] ) {
		if ( empty( $db_resources_ids ) ) {
			return;
		}

		foreach ( $db_resources_ids as $db_resources_id ) {
			$this->warmup_process->push_to_queue( $db_resources_id );
		}

		$this->warmup_process->save()->dispatch();
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

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'remove_unused_css' );
	}

}
