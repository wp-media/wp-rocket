<?php

class ActionScheduler_WPCLI_Scheduler_command extends WP_CLI_Command {

	/**
	 * Run the Action Scheduler
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<size>]
	 * : The maximum number of actions to run. Defaults to 100.
	 *
	 * [--force]
	 * : Whether to force execution despite the maximum number of concurrent processes being exceeded.
	 */
	public function run( $args, $assoc_args ) {
		// Handle passed arguments.
		$batch = \WP_CLI\Utils\get_flag_value( $assoc_args, 'batch-size', 100 );
		$force = \WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		// Set up the class instances we'll need
		$store   = ActionScheduler_Store::instance();
		$monitor = new ActionScheduler_FatalErrorMonitor();
		$cleaner = new ActionScheduler_QueueCleaner();

		// Get the queue runner instance
		$runner = new ActionScheduler_WPCLI_QueueRunner( $store, $monitor, $cleaner );

		// Determine how many tasks will be run.
		$total = $runner->setup( $batch, $force );
		\WP_CLI::line(
			sprintf( _n( 'Found %d scheduled task', 'Found %d scheduled tasks', $total, 'action-scheduler' ), $total )
		);

		$completed = $runner->run();
		\WP_CLI::success(
			sprintf(
				_n( '%d scheduled task completed.', '%d scheduled taskes completed.', $completed, 'action-scheduler' ),
				$completed
			)
		);
	}
}
