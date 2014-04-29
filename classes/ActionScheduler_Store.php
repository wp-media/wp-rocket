<?php

/**
 * Class ActionScheduler_Store
 * @codeCoverageIgnore
 */
abstract class ActionScheduler_Store {
	const STATUS_COMPLETE = 'complete';
	const STATUS_PENDING = 'pending';
	const STATUS_RUNNING = 'in-progress';
	const STATUS_FAILED = 'failed';

	/** @var ActionScheduler_Store */
	private static $store = NULL;

	/**
	 * @param ActionScheduler_Action $action
	 * @param DateTime $date Optional date of the first instance
	 *        to store. Otherwise uses the first date of the action's
	 *        schedule.
	 *
	 * @return string The action ID
	 */
	abstract public function save_action( ActionScheduler_Action $action, DateTime $date = NULL );

	/**
	 * @param string $action_id
	 *
	 * @return ActionScheduler_Action
	 */
	abstract public function fetch_action( $action_id );

	/**
	 * @param string $hook
	 * @param array $params
	 * @return string ID of the next action matching the criteria
	 */
	abstract public function find_action( $hook, $params = array() );

	/**
	 * @param array $query
	 * @return array The IDs of actions matching the query
	 */
	abstract public function query_actions( $query = array() );

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	abstract public function cancel_action( $action_id );

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	abstract public function delete_action( $action_id );

	/**
	 * @param string $action_id
	 *
	 * @return DateTime The date the action is schedule to run, or the date that it ran.
	 */
	abstract public function get_date( $action_id );


	/**
	 * @param int $max_actions
	 * @param DateTime $before_date Claim only actions schedule before the given date. Defaults to now.
	 *
	 * @return ActionScheduler_ActionClaim
	 */
	abstract public function stake_claim( $max_actions = 10, DateTime $before_date = NULL );

	/**
	 * @return int
	 */
	abstract public function get_claim_count();

	/**
	 * @param ActionScheduler_ActionClaim $claim
	 *
	 * @return void
	 */
	abstract public function release_claim( ActionScheduler_ActionClaim $claim );

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	abstract public function unclaim_action( $action_id );

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	abstract public function mark_failure( $action_id );

	/**
	 * @param string $action_id
	 * @return void
	 */
	abstract public function log_execution( $action_id );

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	abstract public function mark_complete( $action_id );

	public function init() {}

	/**
	 * @return ActionScheduler_Store
	 */
	public static function instance() {
		if ( empty(self::$store) ) {
			$class = apply_filters('action_scheduler_store_class', 'ActionScheduler_wpPostStore');
			self::$store = new $class();
		}
		return self::$store;
	}
}
 