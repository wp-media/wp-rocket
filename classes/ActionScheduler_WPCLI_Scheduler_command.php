<?php
/**
 *
 */

class ActionScheduler_WPCLI_Scheduler_command extends WP_CLI_Command {

	/**
	 * Run scheduler
	 *
	 * @subcommand run-scheduler
	 */
	public function run_scheduler( $args, $assoc_args ) {

		// Get the queue runner instance
		$queue_runner = ActionScheduler_QueueRunner::instance();




		// Get scheduled tasks to run
		$scheduled_tasks = array();

		$total = count( $scheduled_tasks );

		\WP_CLI::line( sprintf( _n( 'Found %d scheduled tasks', 'Found %d scheduled task', $total, 'prospress' ), $total ) );

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			sprintf( _n( 'Running %d tasks', 'Running %d tasks', $total, 'prospress' ), number_format_i18n( $total ) ),
			$total
		);

		$counter = 0;

		foreach ( $scheduled_tasks as $scheduled_task ) {
			try {
				// Do something
				$success = true;

				if ( ! $success ) {
					\WP_CLI::warning( 'Some problem happened' );
				} else {
					// Success!
				}

				$counter ++;

				$progress_bar->tick();

				// Every 100 tasks, clean memory
				if ( 0 === ( $counter % 100 ) ) {
					$this->stop_the_insanity();
				}
			} catch ( \Exception $e ) {
				\WP_CLI::error( 'Some big problem happened' );
			}
		}

		$progress_bar->finish();

		\WP_CLI::success( __( 'Scheduled task queue cleared.', 'prospress' ) );
	}
}
