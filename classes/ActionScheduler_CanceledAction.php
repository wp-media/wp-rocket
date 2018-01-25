<?php

/**
 * Class ActionScheduler_CanceledAction
 *
 * Stored action which was canceled and therefore acts like a finished action but should always return a null schedule,
 * regardless of schedule passed to its constructor.
 */
class ActionScheduler_CanceledAction extends ActionScheduler_FinishedAction {

	public function __construct( $hook, array $args = array(), ActionScheduler_Schedule $schedule = NULL, $group = '', $id = NULL, $status = '', $claim_id = '' ) {
		parent::__construct( $hook, $args, $schedule, $group );
		$this->set_schedule( new ActionScheduler_NullSchedule() );
	}
}
