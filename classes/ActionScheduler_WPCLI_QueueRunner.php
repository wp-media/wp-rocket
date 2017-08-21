<?php

/**
 * WP CLI Queue runner.
 *
 * This class can only be called from within a WP CLI instance.
 */
class ActionScheduler_WPCLI_QueueRunner extends ActionScheduler_Abstract_QueueCleaner {

	/** @var array */
	protected $actions;

	/** @var  ActionScheduler_ActionClaim */
	protected $claim;

	/** @var ActionScheduler_QueueCleaner */
	protected $cleaner;

	/** @var string */
	protected $id;

	/** @var ActionScheduler_FatalErrorMonitor */
	protected $monitor;

	/** @var \cli\progress\Bar */
	protected $progress_bar;

	/**
	 * ActionScheduler_WPCLI_QueueRunner constructor.
	 *
	 * @param ActionScheduler_Store             $store
	 * @param ActionScheduler_FatalErrorMonitor $monitor
	 * @param ActionScheduler_QueueCleaner      $cleaner
	 *
	 * @throws Exception When this is not run within WP CLI
	 */
	public function __construct(
		ActionScheduler_Store $store,
		ActionScheduler_FatalErrorMonitor $monitor,
		ActionScheduler_QueueCleaner $cleaner
	) {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			throw new Exception( __( 'The ' . __CLASS__ . ' class can only be run within WP CLI.', 'action-scheduler' ) );
		}

		$this->store   = $store;
		$this->monitor = $monitor;
		$this->cleaner = $cleaner;
	}

	/**
	 * Set up the Queue before processing.
	 *
	 * @author Jeremy Pry
	 *
	 * @param int  $batch_size The batch size to process.
	 * @param bool $force      Whether to force running even with too many concurrent processes.
	 *
	 * @return int
	 */
	public function setup( $batch_size, $force = false ) {
		$this->run_cleanup();
		$this->add_hooks();

		// Check to make sure there aren't too many concurrent processes running.
		$claim_count = $this->store->get_claim_count();
		$too_many    = $claim_count >= apply_filters( 'action_scheduler_queue_runner_concurrent_batches', 5 );
		if ( $too_many ) {
			if ( $force ) {
				WP_CLI::warning( __( 'There are too many concurrent batches, but the run is forced to continue.', 'action-scheduler' ) );
			} else {
				WP_CLI::error( __( 'There are too many concurrent batches.', 'action-scheduler' ) );
			}
		}

		// Stake a claim and store it.
		$this->claim = $this->store->stake_claim( $batch_size );
		$this->monitor->attach( $this->claim );
		$this->actions = $this->claim->get_actions();
		$this->id      = $this->claim->get_id();

		return count( $this->actions );
	}

	/**
	 * Add our hooks to the appropriate actions.
	 *
	 * @author Jeremy Pry
	 */
	protected function add_hooks() {
		add_action( 'action_scheduler_before_execute', array( $this, 'before_execute' ) );
		add_action( 'action_scheduler_after_execute', array( $this, 'after_execute' ) );
		add_action( 'action_scheduler_failed_execution', array( $this, 'action_failed' ) );
	}

	/**
	 * Set up the WP CLI progress bar.
	 *
	 * @author Jeremy Pry
	 */
	protected function setup_progress_bar() {
		$count              = count( $this->actions );
		$this->progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf( _n( 'Running %d task', 'Running %d tasks', $count, 'action-scheduler' ), number_format_i18n( $count ) ),
			$count
		);
	}

	/**
	 * Ensure the progress bar has finished properly.
	 *
	 * @author Jeremy Pry
	 */
	protected function finish_progress_bar() {
		$this->progress_bar->finish();
	}

	/**
	 * Process actions in the queue.
	 *
	 * @author Jeremy Pry
	 * @return int The number of actions processed.
	 */
	public function run() {
		$this->setup_progress_bar();
		foreach ( $this->actions as $action_id ) {
			// Error if we lost the claim.
			$all_actions = array_flip( $this->store->find_actions_by_claim_id( $this->id ) );
			if ( ! array_key_exists( $action_id, $all_actions ) ) {
				$this->finish_progress_bar();
				WP_CLI::error( __( 'The claim has been lost. Aborting.', 'action-scheduler' ) );
			}

			$this->process_action( $action_id );
			$this->progress_bar->tick();
		}

		$completed = $this->progress_bar->current();
		$this->finish_progress_bar();

		return $completed;
	}

	/**
	 * Handle WP CLI message when the action is starting.
	 *
	 * @author Jeremy Pry
	 *
	 * @param $action_id
	 */
	public function before_execute( $action_id ) {
		/* translators: %s refers to the action ID */
		WP_CLI::line( sprintf( __( 'Started processing action %s', 'action-scheduler' ), $action_id ) );
	}

	/**
	 * Handle WP CLI message when the action has completed.
	 *
	 * @author Jeremy Pry
	 *
	 * @param $action_id
	 */
	public function after_execute( $action_id ) {
		/* translators: %s refers to the action ID */
		WP_CLI::line( sprintf( __( 'Completed processing action %s', 'action-scheduler' ), $action_id ) );
	}

	/**
	 * Handle WP CLI message when the action has failed.
	 *
	 * @author Jeremy Pry
	 *
	 * @param int       $action_id
	 * @param Exception $exception
	 */
	public function action_failed( $action_id, $exception ) {
		WP_CLI::error(
			/* translators: %1$s refers to the action ID, %2$s refers to the Exception message */
			sprintf( __( 'Error processing action %1$s: %2$s', 'action-scheduler' ), $action_id, $exception->getMessage() ),
			false
		);
	}
}
