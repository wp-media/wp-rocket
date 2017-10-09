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

		WP_CLI::success(
			sprintf(
				_n( '%d scheduled task completed.', '%d scheduled tasks completed.', $completed, 'action-scheduler' ),
				number_format_i18n( $completed )
			)
		);
	}
}
