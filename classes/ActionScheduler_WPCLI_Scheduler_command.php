<?php

/**
 * Commands for the Action Scheduler by Prospress.
 */
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

		$completed = 0;

		try {
			// Get the queue runner instance
			$runner = new ActionScheduler_WPCLI_QueueRunner();

			// Determine how many tasks will be run.
			$total = $runner->setup( $batch, $force );
			WP_CLI::line(
				sprintf(
					_n( 'Found %d scheduled task', 'Found %d scheduled tasks', $total, 'action-scheduler' ),
					number_format_i18n( $total )
				)
			);

			$completed = $runner->run();
		} catch ( Exception $e ) {
			WP_CLI::error(
				sprintf(
					/* translators: %s refers to the exception error message. */
					__( 'There was an error running the action scheduler: %s', 'action-scheduler' ),
					$e->getMessage()
				)
			);
		}

		$this->print_total_batches( $batches_completed );
		$this->print_success( $actions_completed );
	}
	/**
	 * Print WP CLI message about how many batches of actions were processed.
	 *
	 * @author Jeremy Pry
	 *
	 * @param int $batches_completed
	 */
	protected function print_total_batches( $batches_completed ) {
		WP_CLI::log(
			sprintf(
				/* translators: %d refers to the total number of batches executed */
				_n( '%d batch executed.', '%d batches executed.', $batches_completed, 'action-scheduler' ),
				number_format_i18n( $batches_completed )
			)
		);
	}

	/**
	 * Print a success message with the number of completed actions.
	 *
	 * @author Jeremy Pry
	 *
	 * @param int $actions_completed
	 */
	protected function print_success( $actions_completed ) {
		WP_CLI::success(
			sprintf(
				/* translators: %d refers to the total number of taskes completed */
				_n( '%d scheduled task completed.', '%d scheduled tasks completed.', $actions_completed, 'action-scheduler' ),
				number_format_i18n( $actions_completed )
			)
		);
	}
}
