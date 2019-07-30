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
		do_action( 'action_scheduler_run_queue', 'Async Request' ); // run a queue in the same way as WP Cron, but declare the Async Request context
		$this->maybe_dispatch();
	}

	/**
	 * If the async request runner is allowed, and there are pending actions,
	 * dispatch an async request to process them.
	 */
	public function maybe_dispatch() {
		if ( ! $this->allow() || ! $this->store->has_pending_actions_due() ) {
			return;
		}

		$this->dispatch();
	}

	/**
	 * Allow 3rd party code to disable running actions via async requets.
	 */
	protected function allow() {
		return apply_filters( 'action_scheduler_allow_async_request_runner', true );
	}
}
