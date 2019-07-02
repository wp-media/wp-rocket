<?php


namespace Action_Scheduler\Migration;

/**
 * Class DryRun_ActionMigrator
 *
 * @package Action_Scheduler\Migration
 *
 * @codeCoverageIgnore
 */
class DryRun_ActionMigrator extends ActionMigrator {
	public function migrate( $source_action_id ) {
		do_action( 'action_scheduler/migrate_action_dry_run', $source_action_id );

		return 0;
	}
}
