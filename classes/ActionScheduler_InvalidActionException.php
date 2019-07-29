<?php

/**
 * InvalidAction Exception.
 *
 * Used for identifying actions that are invalid in some way.
 *
 * @package Prospress\ActionScheduler
 */
class ActionScheduler_InvalidActionException extends \InvalidArgumentException implements ActionScheduler_Exception {

	/**
	 * Create a new exception when the action's schedule cannot be fetched.
	 *
	 * @param string $action_id The action ID with bad args.
	 * @return static
	 */
	public static function from_schedule( $action_id, $schedule ) {
		$message = sprintf(
			__( 'Action [%s] has an invalid schedule: %s', 'action-scheduler' ),
			$action_id,
			var_export( $schedule, true )
		);

		return new static( $message );
	}

	/**
	 * Create a new exception when the action's args cannot be decoded to an array.
	 *
	 * @author Jeremy Pry
	 *
	 * @param string $action_id The action ID with bad args.
	 * @return static
	 */
	public static function from_decoding_args( $action_id, $args = array() ) {
		$message = sprintf(
			__( 'Action [%s] has invalid arguments. It cannot be JSON decoded to an array. $args = %s', 'action-scheduler' ),
			$action_id,
			var_export( $args, true )
		);

		return new static( $message );
	}
}
