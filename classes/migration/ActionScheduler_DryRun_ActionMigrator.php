<?php


namespace Action_Scheduler\Migration;

class ActionScheduler_DryRun_ActionMigrator extends ActionScheduler_DryRun_ActionMigrator {
	public function migrate( $source_action_id ) {
		do_action( 'action_scheduler/migrate_action_dry_run', $source_action_id );

		return 0;
	}
}
