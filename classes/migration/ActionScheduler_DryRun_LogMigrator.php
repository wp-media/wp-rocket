<?php


namespace Action_Scheduler\Migration;


class ActionScheduler_DryRun_LogMigrator extends ActionScheduler_LogMigrator {
	public function migrate( $source_action_id, $destination_action_id ) {
		// no-op
	}

}