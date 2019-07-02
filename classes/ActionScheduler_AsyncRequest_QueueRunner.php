<?php
/**
 * ActionScheduler_AsyncRequest_QueueRunner
 */

defined( 'ABSPATH' ) || exit;

/**
 * ActionScheduler_AsyncRequest_QueueRunner class.
 */
class ActionScheduler_AsyncRequest_QueueRunner extends WP_Async_Request {

	/**
	 * Data store for querying actions
	 *
	 * @var ActionScheduler_Store
	 * @access protected
	 */
	protected $store;

	/**
	 * Prefix for ajax hooks
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'as';

	/**
	 * Action for ajax hooks
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'async_request_queue_runner';

	/**
	 * Initiate new async request
	 */
	public function __construct( ActionScheduler_Store $store ) {
		parent::__construct();
		$this->store = $store;
	}

	/**
	 * Handle async requests
	 *
	 * Run a queue, and maybe dispatch another async request to run another queue
	 * if there are still pending actions after completing a queue in this request.
	 */
	protected function handle() {
		do_action( 'action_scheduler_run_queue' ); // run a queue in the exact same way as WP Cron
		$this->maybe_dispatch();
	}

	/**
	 * If there are pending actions, dispatch an async request to process them.
	 */
	public function maybe_dispatch() {
		$pending_actions = $this->store->query_actions( array(
			'date'   => as_get_datetime_object(),
			'status' => ActionScheduler_Store::STATUS_PENDING,
		) );

		if ( $pending_actions ) {
			$this->dispatch();
		}
	}
}
