<?php


namespace Action_Scheduler\Custom_Tables\Migration;


class ActionScheduler_DryRun_ActionMigrator extends ActionScheduler_DryRun_ActionMigrator {
	public function migrate( $source_action_id ) {
		do_action( 'action_scheduler/custom_tables/migrate_action_dry_run', $source_action_id );

		return 0;
	}

}