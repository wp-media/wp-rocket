<?php

/**
 * Class ActionScheduler_FinishedAction
 *
 * Stored action which has finished, which could be because it was cancelled, completed or failed.
 */
class ActionScheduler_FinishedAction extends ActionScheduler_StoredAction {

	public function execute() {
		// don't execute
	}

	public function is_finished() {
		return TRUE;
	}
}
 