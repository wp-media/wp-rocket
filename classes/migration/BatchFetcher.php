<?php


namespace Action_Scheduler\Migration;


use ActionScheduler_Store as Store;

/**
 * Class BatchFetcher
 *
 * @package Action_Scheduler\Migration
 *
 * @codeCoverageIgnore
 */
class BatchFetcher {
	private $store;

	public function __construct( Store $source_store ) {
		$this->store = $source_store;
	}

	/**
	 * @param int $count The number of actions to retrieve
	 *
	 * @return int[] A list of action IDs
	 */
	public function fetch( $count = 10 ) {
		foreach ( $this->get_query_strategies( $count ) as $query ) {
			$action_ids = $this->store->query_actions( $query );
			if ( ! empty( $action_ids ) ) {
				return $action_ids;
			}
		}

		return [];
	}

	private function get_query_strategies( $count ) {
		$now  = as_get_datetime_object();
		$args = [
			'date'     => $now,
			'per_page' => $count,
			'offset'   => 0,
			'orderby'  => 'date',
			'order'    => 'ASC',
		];

		$priorities = [
			Store::STATUS_PENDING,
			Store::STATUS_FAILED,
			Store::STATUS_CANCELED,
			Store::STATUS_COMPLETE,
			Store::STATUS_RUNNING,
			'', // any other unanticipated status
		];

		foreach ( $priorities as $status ) {
			yield wp_parse_args( [
				'status'       => $status,
				'date_compare' => '<=',
			], $args );
			yield wp_parse_args( [
				'status'       => $status,
				'date_compare' => '>=',
			], $args );
		}
	}
}